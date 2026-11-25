<?php

namespace Tests\Feature;

use App\Exceptions\PtcStockException;
use App\Models\Stock;
use App\Models\User;
use App\Repositories\PtcStockRepository;
use App\Repositories\StockItemRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-14 / D3: material stock never goes negative. Every material stock-out (issuance, machine
 * issue, PTC material line, material processing) checks availability under a row lock on the
 * material, in the same transaction as the save.
 */
class MaterialStockOutTest extends TestCase
{
    use DatabaseTransactions;

    private int $materialId;

    private float $onHand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
        $repo = app(StockItemRepository::class);
        foreach (DB::table('materials')->orderBy('material_id')->pluck('material_id') as $id) {
            if ($repo->materialStock((int) $id) >= 2) {
                $this->materialId = (int) $id;
                $this->onHand = $repo->materialStock((int) $id);

                return;
            }
        }
        $this->markTestSkipped('needs a material with at least 2 in stock');
    }

    private function stockNow(): float
    {
        return app(StockItemRepository::class)->materialStock($this->materialId);
    }

    private function issue(float $quantity, array $extra = [])
    {
        return $this->post(route('stock.store'), array_merge([
            'stock_no' => 'ZZM-'.uniqid(),
            'order_id' => (int) DB::table('orders')->value('order_id'),
            'employee_id' => (int) DB::table('employees')->value('employee_id'),
            'table_name' => 'employee',
            'stock_date' => now()->toDateString(),
            'issue_for' => 0,
            'product_type_id' => [0],
            'material_id' => [$this->materialId],
            'stage_id' => [0],
            'quantity' => [$quantity],
            'work_logs' => [0],
        ], $extra));
    }

    public function test_issuing_more_material_than_on_hand_is_refused(): void
    {
        $stocks = Stock::count();

        $this->issue($this->onHand + 1)->assertSessionHas('fails', fn ($m) => str_contains($m, 'in stock') && str_contains($m, 'requested'));

        $this->assertSame($stocks, Stock::count(), 'nothing was saved');
        $this->assertEqualsWithDelta($this->onHand, $this->stockNow(), 0.0001);
    }

    public function test_issuing_exactly_what_is_on_hand_is_allowed_and_the_next_issue_is_refused(): void
    {
        $this->issue($this->onHand - 1)->assertSessionHasNoErrors()->assertSessionMissing('fails');
        $this->issue(1)->assertSessionMissing('fails');
        $this->assertEqualsWithDelta(0, $this->stockNow(), 0.0001);

        $this->issue(0.5)->assertSessionHas('fails');
        $this->assertEqualsWithDelta(0, $this->stockNow(), 0.0001);
    }

    public function test_editing_an_issuance_above_what_is_on_hand_is_refused(): void
    {
        $this->issue(1)->assertSessionMissing('fails');
        $stock = Stock::orderByDesc('stock_id')->firstOrFail();

        $this->post(route('stock.update', $stock->stock_id), [
            'stock_no' => $stock->stock_no, 'order_id' => $stock->order_id, 'employee_id' => $stock->employee_id,
            'table_name' => 'employee', 'stock_date' => $stock->stock_date, 'issue_for' => 0,
            'product_type_id' => [0], 'material_id' => [$this->materialId], 'stage_id' => [0],
            'quantity' => [$this->onHand + 5], 'work_logs' => [0],
        ])->assertSessionHas('fails');

        $this->assertEqualsWithDelta($this->onHand - 1, $this->stockNow(), 0.0001, 'the edit was rolled back');
    }

    public function test_machine_material_issue_above_stock_is_refused(): void
    {
        $machineId = (int) DB::table('machines')->value('machine_id');
        if (! $machineId) {
            $this->markTestSkipped('needs a machine');
        }

        $this->issue($this->onHand + 1, ['machine_id' => $machineId])->assertSessionHas('fails');
        $this->assertEqualsWithDelta($this->onHand, $this->stockNow(), 0.0001);
    }

    public function test_ptc_material_line_above_stock_is_refused(): void
    {
        $ptcId = (int) Stock::where('is_ptc_master', 1)->where('stock_status', Stock::STATUS_PTC_IN_PROGRESS)->value('stock_id');
        if (! $ptcId) {
            $this->markTestSkipped('needs an open PTC');
        }
        $ptc = Stock::find($ptcId);

        try {
            app(PtcStockRepository::class)->issue($ptcId, [
                'stock_no' => 'ZZPTC-'.uniqid(), 'stock_date' => now()->toDateString(), 'employee_id' => $ptc->employee_id,
                'table_name' => 'employee', 'description' => '',
            ], [['product_type_id' => 0, 'material_id' => $this->materialId, 'component_id' => null, 'stage_id' => 0, 'quantity' => $this->onHand + 1]]);
            $this->fail('the PTC issue should have been refused');
        } catch (PtcStockException $e) {
            $this->assertStringContainsString('in stock', $e->getMessage());
        }
        $this->assertEqualsWithDelta($this->onHand, $this->stockNow(), 0.0001);
    }

    public function test_material_processing_above_stock_is_refused(): void
    {
        $before = DB::table('purchases')->count();

        $this->post(route('mprocess.store'), [
            'purchase_no' => 'ZZMP-'.uniqid(), 'purchase_type' => 'mprocess', 'vendor_id' => (int) DB::table('vendors')->value('vendor_id'),
            'order_id' => 0, 'purchase_date' => now()->toDateString(), 'require_date' => now()->toDateString(),
            'amaterial_id' => [$this->materialId], 'aquantity' => [$this->onHand + 1],
            'bmaterial_id' => [$this->materialId], 'bquantity' => [1], 'price' => [1], 'total' => [1],
        ])->assertSessionHas('fails');

        $this->assertSame($before, DB::table('purchases')->count(), 'nothing was saved');
        $this->assertEqualsWithDelta($this->onHand, $this->stockNow(), 0.0001);
    }

    public function test_availability_is_read_under_a_row_lock_on_the_material(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        $this->issue(1)->assertSessionMissing('fails');

        $lock = collect($queries)->search(fn ($sql) => str_contains($sql, 'from `materials`') && str_contains($sql, 'for update'));
        $this->assertNotFalse($lock, 'materials row lock taken');
        $firstLine = collect($queries)->search(fn ($sql) => str_starts_with($sql, 'insert into `stock_items`'));
        $this->assertLessThan($firstLine, $lock, 'lock taken before the lines are written');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\StockItemRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * F-11 Low Stock card, F-12 Expense This Month, F-39 Running PTCs, F-22 receipt print status,
 * F-27 formula injection in the invoice Excel.
 */
class ReportCorrectnessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
    }

    public function test_low_stock_card_counts_product_sizes_below_50_from_the_stock_formula(): void
    {
        // Independent figure: /stock per product size, all stages except Rejection (105), below 50
        $expected = app(StockItemRepository::class)->pStock()
            ->groupBy('product_type_id')
            ->filter(fn ($rows) => $rows->sum(fn ($r) => (int) $r->stage_id === 105 ? 0 : (float) $r->stockIn - (float) $r->stockOut) < 50)
            ->count();

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });
        $card = $this->get(route('dashboard'))->assertOk()->viewData('lowStockAlerts');

        $this->assertSame($expected, $card);
        $this->assertLessThan(35, $queries, 'one stock query instead of one per product size');
    }

    public function test_expense_this_month_is_expenses_minus_reversals(): void
    {
        $card = fn () => (float) $this->get(route('dashboard'))->viewData('expenseThisMonth');
        $before = $card();
        $head = (int) DB::table('heads')->where('head_type_id', 7)->value('head_id');
        $today = Carbon::now()->toDateString();
        foreach ([['credit' => 1000], ['debit' => 1000], ['credit' => 300]] as $amount) {
            Transaction::create(['transaction_to' => 'expense', 'transaction_type' => 'expense', 'transaction_date' => $today,
                'bank_id' => 0, 'payee_id' => $head, 'ledger_flag' => 1, 'created_by' => 1] + $amount);
        }

        $this->assertEqualsWithDelta($before + 300, $card(), 0.001, '1000 spent, 1000 reversed, 300 spent = 300');
    }

    public function test_running_ptcs_table_lists_each_ptc_once(): void
    {
        // a PTC with several lines used to appear once per line
        $ptc = DB::table("stocks")->where("is_ptc_master", 1)->where("stock_status", 6)->orderByDesc("stock_date")->first();
        $line = DB::table("stock_items")->where("stock_id", $ptc->stock_id)->where("product_type_id", ">", 0)->first();
        DB::table("stock_items")->insert(["stock_id" => $ptc->stock_id, "product_type_id" => (int) DB::table("product_types")->where("product_type_id", "!=", $line->product_type_id)->value("product_type_id"), "material_id" => 0, "stage_id" => $line->stage_id, "quantity" => 1, "work_logs" => 0, "work_wages" => 0, "created_by" => 1]);

        $rows = $this->get(route('dashboard'))->viewData('runningPtcsTable');

        $this->assertSame($rows->count(), $rows->pluck('stock_id')->unique()->count(), 'no PTC twice');
        $this->assertLessThanOrEqual(10, $rows->count());
        $open = DB::table('stocks')->where('is_ptc_master', 1)->where('stock_status', 6)->count();
        $this->assertSame(min(10, $open), $rows->count(), 'up to 10 PTCs, not 10 rows');
    }

    public function test_receipt_print_shows_the_real_receiving_status(): void
    {
        $purchaseId = DB::table('purchases')->insertGetId(['purchase_no' => 'ZZP-'.uniqid(), 'purchase_type' => 'material', 'order_id' => 0,
            'vendor_id' => (int) DB::table('vendors')->value('vendor_id'), 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'created_by' => 1]);
        $itemId = DB::table('purchase_items')->insertGetId(['purchase_id' => $purchaseId, 'product_type_id' => 0, 'product_stage_id' => 0,
            'material_id' => (int) DB::table('materials')->value('material_id'), 'quantity' => 100, 'price' => 1, 'total' => 100, 'created_by' => 1]);
        $receipt = function (float $qty) use ($purchaseId, $itemId) {
            $id = DB::table('receives')->insertGetId(['receive_no' => 'ZZR-'.uniqid(), 'purchase_id' => $purchaseId, 'receive_date' => '2026-01-02', 'created_by' => 1]);
            DB::table('receive_materials')->insert(['receive_id' => $id, 'purchase_item_id' => $itemId, 'quantity' => $qty, 'approved_qty' => $qty,
                'pending_qty' => 0, 'rejected_qty' => 0, 'inspection_date' => '2026-01-02', 'created_by' => 1]);

            return $id;
        };

        $first = $receipt(60);
        $this->get(route('receive.print', $first))->assertOk()->assertSee('Partially Received');
        $second = $receipt(40);
        $this->get(route('receive.print', $second))->assertOk()->assertSee('Completely Received');
        $this->get(route('receive.print', $first))->assertOk()->assertSee('Partially Received');
    }

    public function test_invoice_excel_writes_party_text_as_text_not_formulas(): void
    {
        $orderId = (int) DB::table('order_items')->where('price', '>', 0)->value('order_id');
        $customerId = (int) DB::table('orders')->where('order_id', $orderId)->value('customer_id');
        DB::table('customers')->where('customer_id', $customerId)->update(['address' => '=HYPERLINK("http://evil.test","x")']);

        $response = $this->get(route('order.proforma.excel', ['id' => $orderId, 'bank_id' => DB::table('banks')->value('bank_id')]));
        $file = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
        file_put_contents($file, $response->streamedContent());
        $sheet = IOFactory::load($file)->getActiveSheet();

        $found = false;
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                if (str_contains((string) $cell->getValue(), 'HYPERLINK')) {
                    $found = true;
                    $this->assertSame(DataType::TYPE_STRING, $cell->getDataType(), 'address cell must be text');
                }
            }
        }
        $this->assertTrue($found, 'address written to the workbook');
    }
}

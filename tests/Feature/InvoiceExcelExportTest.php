<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Support\InvoiceTotals;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

/**
 * Commercial / proforma invoices exported to Excel match the printed invoice.
 * Rolled back after each test.
 */
class InvoiceExcelExportTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->firstOrFail();
    }

    /** Downloads a workbook and returns its sheet with formulas recalculated. */
    private function workbook(string $url): Worksheet
    {
        $response = $this->get($url);
        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));

        $file = tempnam(sys_get_temp_dir(), 'inv').'.xlsx';
        file_put_contents($file, $response->streamedContent());
        $sheet = IOFactory::load($file)->getActiveSheet();
        @unlink($file);

        return $sheet;
    }

    /** Value (calculated) of the cell right of / in the row whose column A text starts with $label. */
    private function rowValue(Worksheet $sheet, string $label, string $column, bool $exact = false)
    {
        foreach ($sheet->getRowIterator() as $row) {
            $r = $row->getRowIndex();
            $text = trim((string) $sheet->getCell("A{$r}")->getValue());
            if ($text === $label || (! $exact && str_starts_with($text, $label.' ')) || str_starts_with($text, $label.'(') || str_starts_with($text, $label.':')) {
                return $sheet->getCell("{$column}{$r}")->getCalculatedValue();
            }
        }
        $this->fail("row '{$label}' not found");
    }

    private function allText(Worksheet $sheet): string
    {
        $text = '';
        foreach ($sheet->toArray(null, false, false) as $row) {
            $text .= implode(' | ', array_map('strval', $row))."\n";
        }

        return $text;
    }

    public function test_commercial_invoice_excel_matches_the_print(): void
    {
        $this->actingAs($this->admin());
        $delivery = DB::table('deliveries')->first();
        DB::table('deliveries')->where('delivery_id', $delivery->delivery_id)->update(['freight_charges' => 125.5]);

        $items = app(StockItemRepository::class)->delivery($delivery->delivery_id);
        $expected = InvoiceTotals::commercial($items, ['freight_charges' => 125.5], null, 100);

        // The print shows these figures
        $print = $this->get(route('delivery.commercial', $delivery->delivery_id))->assertOk();
        $print->assertSee(number_format($expected['grandTotal'], 2));
        $print->assertSee(number_format($expected['itemsTotal'], 2));

        $sheet = $this->workbook(route('delivery.commercial.excel', $delivery->delivery_id));
        $text = $this->allText($sheet);
        $this->assertStringContainsString('COMMERCIAL INVOICE', $text);
        $this->assertStringContainsString((string) $delivery->delivery_no, $text);

        $last = $sheet->getHighestColumn();
        $this->assertEqualsWithDelta($expected['itemsTotal'], $this->rowValue($sheet, 'Items Total', $last), 0.001);
        $this->assertEqualsWithDelta(125.5, $this->rowValue($sheet, 'Freight Charges', $last), 0.001);
        $amountCol = chr(ord($last) - 1);
        $this->assertEqualsWithDelta($expected['grandTotal'], $this->rowValue($sheet, 'Grand Total', $amountCol), 0.001);

        // Line totals are live formulas (editable workbook)
        $firstLine = null;
        foreach ($sheet->getRowIterator() as $row) {
            $r = $row->getRowIndex();
            if ((string) $sheet->getCell("A{$r}")->getValue() === '1') {
                $firstLine = $r;
                break;
            }
        }
        $this->assertNotNull($firstLine);
        $this->assertStringStartsWith('=', (string) $sheet->getCell("{$last}{$firstLine}")->getValue());
    }

    public function test_partial_payment_due_matches_the_print(): void
    {
        $this->actingAs($this->admin());
        $delivery = DB::table('deliveries')->first();
        $items = app(StockItemRepository::class)->delivery($delivery->delivery_id);
        $expected = InvoiceTotals::commercial($items, (array) $delivery, null, 50);

        $this->get(route('delivery.commercial', ['id' => $delivery->delivery_id, 'payment_percent' => 50]))
            ->assertOk()->assertSee(number_format($expected['amountDue'], 2));

        $sheet = $this->workbook(route('delivery.commercial.excel', ['id' => $delivery->delivery_id, 'payment_percent' => 50, 'selling_type' => 'FOB']));
        $amountCol = chr(ord($sheet->getHighestColumn()) - 1);
        $this->assertEqualsWithDelta($expected['amountDue'], $this->rowValue($sheet, 'Payment Due', $amountCol), 0.001);
        $this->assertStringContainsString('Invoice Value (Total FOB Amount)', $this->allText($sheet));
    }

    public function test_proforma_invoice_excel_matches_the_print(): void
    {
        $this->actingAs($this->admin());
        $orderId = DB::table('order_items')->where('price', '>', 0)->value('order_id');
        $items = app(OrderItemRepository::class)->get($orderId);
        $expected = InvoiceTotals::proforma($items, null);
        $bankId = DB::table('banks')->value('bank_id');

        $this->get(route('order.proforma', $orderId))->assertOk()->assertSee(number_format($expected['grandTotal'], 2));

        $sheet = $this->workbook(route('order.proforma.excel', ['id' => $orderId, 'bank_id' => $bankId, 'hs_code' => '9018.90']));
        $text = $this->allText($sheet);
        $this->assertStringContainsString('PROFORMA INVOICE', $text);
        $this->assertStringContainsString('PI-'.$orderId, $text);
        $this->assertStringContainsString('9018.90', $text);
        $this->assertStringContainsString('Bank Account Details', $text);
        $this->assertStringContainsString('CERTIFIED TO BE TRUE AND CORRECT', $text);

        $last = $sheet->getHighestColumn();
        $this->assertEqualsWithDelta($expected['grandTotal'], $this->rowValue($sheet, 'Total', $last, true), 0.001);
        $amountCol = chr(ord($last) - 1);
        $this->assertEqualsWithDelta($expected['grandTotal'], $this->rowValue($sheet, 'Grand Total', $amountCol), 0.001);
        $this->assertSame($items->count(), collect($sheet->toArray(null, false, false))->filter(fn ($row) => is_numeric($row[0] ?? null) && (int) $row[0] >= 1 && is_numeric($row[4] ?? null))->count());
    }

    public function test_export_needs_view_permission(): void
    {
        $role = \App\Models\Role::create(['name' => 'No invoices '.uniqid()]);
        $user = User::create(['name' => 'X', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
        $this->actingAs($user)->get(route('delivery.commercial.excel', DB::table('deliveries')->value('delivery_id')))->assertForbidden();
        $this->actingAs($user)->get(route('order.proforma.excel', DB::table('orders')->value('order_id')))->assertForbidden();
    }
}

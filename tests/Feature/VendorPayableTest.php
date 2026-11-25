<?php

namespace Tests\Feature;

use App\Repositories\TransactionRepository;
use App\Repositories\VendorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * D1 / F-07: the vendor is billed for the APPROVED quantity only (rejected / pending are not payable).
 * Vendor balance (/vendor) and vendor ledger use the same definition.
 */
class VendorPayableTest extends TestCase
{
    use DatabaseTransactions;

    public function test_vendor_payable_uses_approved_quantity(): void
    {
        $vendorId = (int) DB::table('vendors')->where('vendor_type', '0')->value('vendor_id');
        $balance = fn () => (float) app(VendorRepository::class)->vendorWithBalance()->firstWhere('vendor_id', $vendorId)->balance;
        $ledgerDebit = fn ($rows) => (float) collect($rows)->where('purchase_id', $purchaseId ?? 0)->sum('debit');
        $before = $balance();

        $purchaseId = DB::table('purchases')->insertGetId(['purchase_no' => 'ZZP-'.uniqid(), 'purchase_type' => 'material', 'order_id' => 0,
            'vendor_id' => $vendorId, 'purchase_date' => '2026-02-01', 'require_date' => '2026-02-01', 'created_by' => 1]);
        $itemId = DB::table('purchase_items')->insertGetId(['purchase_id' => $purchaseId, 'product_type_id' => 0, 'product_stage_id' => 0,
            'material_id' => (int) DB::table('materials')->value('material_id'), 'quantity' => 10, 'price' => 2, 'total' => 20, 'created_by' => 1]);
        $receiveId = DB::table('receives')->insertGetId(['receive_no' => 'ZZR-'.uniqid(), 'purchase_id' => $purchaseId, 'receive_date' => '2026-02-02', 'created_by' => 1]);
        DB::table('receive_materials')->insert(['receive_id' => $receiveId, 'purchase_item_id' => $itemId, 'quantity' => 10, 'approved_qty' => 6,
            'rejected_qty' => 3, 'pending_qty' => 1, 'inspection_date' => '2026-02-02', 'created_by' => 1]);

        $this->assertEqualsWithDelta($before + 12, $balance(), 0.0001, 'vendor balance: 6 approved x 2');

        $repo = app(TransactionRepository::class);
        $this->assertEqualsWithDelta(12, (float) collect($repo->vDetail($vendorId))->where('purchase_id', $purchaseId)->whereNull('transaction_id')->sum('debit'), 0.0001, 'ledger');
        $filtered = $repo->vDetailFilter($vendorId, '2026-02-01', '2026-02-28');
        $rows = collect($filtered['transactions'])->where('purchase_id', $purchaseId)->whereNull('transaction_id');
        $this->assertEqualsWithDelta(12, (float) $rows->sum('debit'), 0.0001, 'ledger with dates');
        $march = $repo->vDetailFilter($vendorId, '2026-03-01', '2026-03-31');
        $this->assertEqualsWithDelta(12, (float) $march['opening_balance'] - (float) $filtered['opening_balance'] - (float) collect($filtered['transactions'])->whereNotNull('transaction_id')->sum(fn ($t) => (float) $t->debit - (float) $t->credit), 0.0001, 'opening balance');
    }
}

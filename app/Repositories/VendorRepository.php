<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Repositories\ImageRepository;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class VendorRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        $vendors = Vendor::select('vendors.*')
            ->orderBy('vendors.created_at', 'desc')->get();

        // Add vendor type names for each vendor
        foreach ($vendors as $vendor) {
            if ($vendor->vendor_type_id) {
                $vendor->vtname = $this->getVendorTypeNames($vendor->vendor_type_id);
            } else {
                $vendor->vtname = '';
            }
        }

        return $vendors;
    }

    public function worker() // Contractors
    {
        $vendors = Vendor::select('vendors.*')
            ->orderBy('vendors.created_at', 'desc')
            ->where('vendor_type', '1')->get();

        // Add vendor type names for each vendor
        foreach ($vendors as $vendor) {
            if ($vendor->vendor_type_id) {
                $vendor->vtname = $this->getVendorTypeNames($vendor->vendor_type_id);
            } else {
                $vendor->vtname = '';
            }
        }

        return $vendors;
    }

    public function vendor() // Without Balance
    {
        $vendors = Vendor::select('vendors.*')
            ->orderBy('vendors.created_at', 'desc')
            ->where('vendor_type', '0')->get();

        // Add vendor type names for each vendor
        foreach ($vendors as $vendor) {
            if ($vendor->vendor_type_id) {
                $vendor->vtname = $this->getVendorTypeNames($vendor->vendor_type_id);
            } else {
                $vendor->vtname = '';
            }
        }

        return $vendors;
    }

    public function vendorWithBalance()
    {
        $vendors = Vendor::select('vendors.*')
            ->where('vendor_type', '0')
            ->orderBy('vendors.created_at', 'desc')
            ->get();

        foreach ($vendors as $vendor) {
            $purchases = \DB::table('purchases') // Amount of Received Purchase Items
                ->select(\DB::raw('SUM(receive_materials.quantity * purchase_items.price) as credit'))
                ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
                ->join('receive_materials', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
                ->where('purchases.vendor_id', $vendor->vendor_id)
                ->groupBy('purchases.vendor_id')
                ->first();

            $purchaseReturns = \DB::table('returns')
                ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
                ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
                ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
                ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
                ->select(\DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
                ->where('purchases.vendor_id', $vendor->vendor_id)
                ->groupBy('purchases.vendor_id')
                ->first();

            $transactions = \DB::table('transactions')
                ->select(
                    DB::raw('SUM(CASE WHEN transactions.transaction_type != "wages" THEN transactions.credit ELSE 0 END) AS totalCredit'),
                    DB::raw('SUM(CASE WHEN transactions.transaction_type != "wages" THEN transactions.debit ELSE 0 END) AS totalDebit')
                )
                ->where('transactions.transaction_to', 'vendor')
                ->where('transactions.payee_id', $vendor->vendor_id)
                ->first();

            $totalCredit = ($purchases->credit ?? 0) + ($transactions->totalCredit ?? 0);
            $totalDebit = ($purchaseReturns->debit ?? 0) + ($transactions->totalDebit ?? 0);
            $vendor->balance = $totalCredit - $totalDebit;

            // Add vendor type names
            if ($vendor->vendor_type_id) {
                $vendor->vtname = $this->getVendorTypeNames($vendor->vendor_type_id);
            } else {
                $vendor->vtname = '';
            }
        }

        return $vendors;
    }

    public function get($id)
    {
        $vendor = Vendor::where('vendor_id', $id)
            ->join('heads as chead', 'chead.head_id', '=', 'vendors.city_id')
            ->select('vendors.*', 'chead.name as cname')
            ->first();

        if ($vendor && $vendor->vendor_type_id) {
            $vendor->vtname = $this->getVendorTypeNames($vendor->vendor_type_id);
        }

        return $vendor;
    }

    private function getVendorTypeNames($vendorTypeIds)
    {
        if (empty($vendorTypeIds)) {
            return '';
        }

        $typeIds = explode('|', $vendorTypeIds);
        $types = DB::table('heads')
            ->whereIn('head_id', $typeIds)
            ->pluck('name')
            ->toArray();

        return implode(', ', $types);
    }

    public function refNo()
    {
        $lastVendor = Vendor::where('vendor_no', 'like', 'V%')->max('vendor_no');
        $lastNumber = $lastVendor ? intval(substr($lastVendor, 1)) : 0;

        return 'V'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function refNo2()
    {
        $lastVendor = Vendor::where('vendor_no', 'like', 'C%')->max('vendor_no');
        $lastNumber = $lastVendor ? intval(substr($lastVendor, 1)) : 0;

        return 'C'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Vendor::create($data);

        return $store->vendor_id;
    }

    public function update($id, array $data)
    {
        $update = Vendor::findOrFail($id);
        $update->update($data);

        return $update->vendor_id;
    }

    /**
     * Contractors are vendors rows with vendor_type 1.
     */
    public function isContractor($id): bool
    {
        return (string) Vendor::findOrFail($id)->vendor_type === '1';
    }

    /**
     * Vendors and contractors (vendor_type 1) can be deleted only while nothing uses them;
     * their own opening balance entry and images are deleted with them.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $vendor = Vendor::findOrFail($id);
        $party = (string) $vendor->vendor_type === '1' ? 'contractor' : 'vendor';
        $impact = DeletionImpact::for(ucfirst($party).' '.$vendor->vendor_no.' ('.$vendor->name.')');
        $vendorStock = fn ($q) => $q->where('table_name', 'vendor');
        $ledger = fn ($q) => $q->whereIn('transaction_to', ['vendor', 'contractor'])->where('transaction_type', '!=', 'openingBalance');
        $opening = fn ($q) => $q->whereIn('transaction_to', ['vendor', 'contractor'])->where('transaction_type', 'openingBalance');

        $impact->blockIf($this->refCount('purchases', 'vendor_id', $id), 'purchase', null, $this->refExamples('purchases', 'vendor_id', $id, 'purchase_no'));
        $impact->blockIf($this->refCount('materials', 'vendor_id', $id), 'material (default vendor)', 'materials (default vendor)', $this->refExamples('materials', 'vendor_id', $id, 'name'));
        $impact->blockIf($this->refCount('stocks', 'employee_id', $id, $vendorStock), 'issuance / receiving / PTC record', 'issuance / receiving / PTC records', $this->refExamples('stocks', 'employee_id', $id, 'stock_no', $vendorStock));
        $impact->blockIf($this->refCount('transactions', 'payee_id', $id, $ledger), 'ledger transaction (payment / journal voucher)', 'ledger transactions (payments / journal vouchers)');
        $impact->blockIf($this->refCount('banks', 'banker_id', $id, fn ($q) => $q->whereIn('bank_holder', ['vendor', 'contractor'])), 'bank account');

        $impact->cascadeIf($this->refCount('transactions', 'payee_id', $id, $opening), 'opening balance entry', 'opening balance entries');
        $impact->cascadeIf(app(ImageRepository::class)->countFor('vendors', $id), 'image');

        return $impact;
    }

    public function delete($id)
    {
        DB::table('transactions')->whereIn('transaction_to', ['vendor', 'contractor'])->where('transaction_type', 'openingBalance')->where('payee_id', $id)->delete();
        app(ImageRepository::class)->deleteFor('vendors', $id);

        return Vendor::findOrFail($id)->delete();
    }
}

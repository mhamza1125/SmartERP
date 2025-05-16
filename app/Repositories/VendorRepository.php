<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class VendorRepository implements GlobalInterface
{
    public function all()
    {
        return Vendor::leftJoin('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
            ->select('vendors.*', 'vthead.name as vtname')
            ->orderBy('vendors.created_at', 'desc')->get();
    }

    public function worker() // Contractors
    {
        return Vendor::leftJoin('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
            ->select('vendors.*', 'vthead.name as vtname')
            ->orderBy('vendors.created_at', 'desc')
            ->where('vendor_type', '1')->get();
    }

    public function vendor() // Without Balance
    {
        return Vendor::join('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
            ->select('vendors.*', 'vthead.name as vtname')
            ->orderBy('vendors.created_at', 'desc')
            ->where('vendor_type', '0')->get();
    }

    public function vendorWithBalance()
    {
        $vendors = Vendor::join('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
            ->select('vendors.*', 'vthead.name as vtname')
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
        }

        return $vendors;
    }

    public function get($id)
    {
        return Vendor::where('vendor_id', $id)
            ->leftJoin('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
            ->join('heads as chead', 'chead.head_id', '=', 'vendors.city_id')
            ->select('vendors.*', 'vthead.name as vtname', 'chead.name as cname')
            ->first();
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

    public function delete($id)
    {
    }
}

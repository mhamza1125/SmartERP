<?php

namespace App\Repositories\Operator;

use App\Models\VendorPayment;

class VendorPaymentRepository implements GlobalInterface {
    
    public function all(){
        return VendorPayment::join('vendors', 'vendors.vendor_id', '=', 'vendor_payments.vendor_id')
        ->join('vendor_types', 'vendor_types.vt_id', '=', 'vendors.vt_id')
        ->select('vendors.*', 'vendor_payments.*', 'vendor_types.name as vtname')
        ->orderBy('vendor_payments.created_at', 'desc')
        ->get();
    }

    public function get($id){
        $payments = VendorPayment::join('vendors', 'vendors.vendor_id', '=', 'vendor_payments.vendor_id')
            ->join('vendor_types', 'vendors.vt_id', '=', 'vendor_types.vt_id')
            ->select('vendors.*', 'vendor_payments.*')
            ->where('vendor_payments.vendor_id', $id)
            ->get();
        
        $tdebit = $payments->sum('debit');
        $tcredit = $payments->sum('credit');
        
        return [
            'payments' => $payments,
            'tdebit' => $tdebit,
            'tcredit' => $tcredit,
        ];
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $vp_id = VendorPayment::create($data);
        return $vp_id->vp_id;
    }

    public function update($id, array $data) {
        $vendorPay = VendorPayment::where('transaction_type', 5)->findOrFail($id);
        $vendorPay->update($data);
    }

    public function updatePurchase($id, array $data) {
        $vendorPay = VendorPayment::where('purchase_id', $id);
        $vendorPay->update($data);
    } 

    public function updatePay($id, array $data) {
        $vendorPayment = VendorPayment::findOrFail($id);
        $vendorPayment->update($data);
    }

    public function delete($id){
        VendorPayment::where('purchase_id', $id)->delete();
    }
}

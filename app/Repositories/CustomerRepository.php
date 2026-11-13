<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Repositories\ImageRepository;
use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Customer::leftJoin('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
            ->select('customers.*', 'currency.name as currency_name')
            ->orderBy('customers.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return Customer::where('customer_id', $id)
            ->join('heads as country', 'country.head_id', '=', 'customers.country_id')
            ->join('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
            ->select('*', 'country.name as coname', 'currency.name as cuname')->first();
    }

    public function refNo()
    {
        $lastCustomer = Customer::all()->sortByDesc(function ($customer) {
            return intval(substr($customer->customer_no, 2)); // skip "CU"
        })->first();
        $lastNumber = $lastCustomer ? intval(substr($lastCustomer->customer_no, 2)) : 0;
        
        return 'CU' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);        

        // $year = Carbon::now()->format('y');
        // $count = Customer::whereYear('created_at', Carbon::now()->year)->count();
        // $threeDigitNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        // return 'CU'.$year.$threeDigitNumber;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Customer::create($data);

        return $store->customer_id;
    }

    public function update($id, array $data)
    {
        $update = Customer::findOrFail($id);
        $update->update($data);

        return $update->customer_id;
    }

    /**
     * A customer can be deleted only while no order or ledger entry uses it; its own opening
     * balance entry and images are deleted with it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $customer = Customer::findOrFail($id);
        $impact = DeletionImpact::for('Customer '.$customer->customer_no.' ('.trim($customer->fname.' '.$customer->lname).')');

        $impact->blockIf($this->refCount('orders', 'customer_id', $id), 'order', null, $this->refExamples('orders', 'customer_id', $id, 'order_no'));
        $impact->blockIf(
            $this->refCount('transactions', 'payee_id', $id, fn ($q) => $q->where('transaction_to', 'customer')->where('transaction_type', '!=', 'openingBalance')),
            'ledger transaction (payment / journal voucher)', 'ledger transactions (payments / journal vouchers)'
        );
        $impact->blockIf($this->refCount('banks', 'banker_id', $id, fn ($q) => $q->where('bank_holder', 'customer')), 'bank account');

        $impact->cascadeIf($this->refCount('transactions', 'payee_id', $id, fn ($q) => $q->where('transaction_to', 'customer')->where('transaction_type', 'openingBalance')), 'opening balance entry', 'opening balance entries');
        $impact->cascadeIf(app(ImageRepository::class)->countFor('customers', $id), 'image');

        return $impact;
    }

    public function delete($id)
    {
        DB::table('transactions')->where('transaction_to', 'customer')->where('transaction_type', 'openingBalance')->where('payee_id', $id)->delete();
        app(ImageRepository::class)->deleteFor('customers', $id);

        return Customer::findOrFail($id)->delete();
    }
}

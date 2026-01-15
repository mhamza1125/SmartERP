@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Payment Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addOPayment') }}" class="btn btn-primary">Add Payment</a>
            </div>
          </div>
          <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('oPayment.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
              @csrf
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom ?? ''}}">
                </div>
                <div class="form-group col-md-3">
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto ?? ''}}">
                </div>
                <div class="form-group col-md-4">
                  <label>Customer</label>
                  <select class="form-control select2" name="customer_id">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                      <option value="{{ $customer->customer_id }}" {{ (isset($customer_id) && $customer_id == $customer->customer_id) ? 'selected' : '' }}>
                        {{ $customer->customer_no }} - {{ $customer->fname }} {{ $customer->lname }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                  @if(!empty($dfrom) || !empty($dto) || !empty($customer_id))
                    <a href="{{ route('oPayment') }}" class="btn btn-secondary mt-2">Clear</a>
                  @endif
                </div>
              </div>
            </form>

            @if(!empty($dfrom) || !empty($dto) || !empty($customer_id))
              <div class="alert alert-info">
                <strong>Filtered Results:</strong>
                @if(!empty($dfrom) && !empty($dto))
                  Showing payments from {{ date("d-m-Y", strtotime($dfrom)) }} to {{ date("d-m-Y", strtotime($dto)) }}
                @endif
                @if(!empty($customer_id))
                  @php
                    $selectedCustomer = $customers->firstWhere('customer_id', $customer_id);
                  @endphp
                  @if($selectedCustomer)
                    for customer: {{ $selectedCustomer->customer_no }} - {{ $selectedCustomer->fname }} {{ $selectedCustomer->lname }}
                  @endif
                @endif
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Customer No</th>
                    <th>Order No</th>
                    <th>Amount</th>
                    <th>Fees</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>SLE-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                      <td>{{\Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y')}}</td>
                      <td>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</td>
                      <td>{{$item->order_no}}</td>
                      <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>
                      <td>{{$item->fb_charges ? number_format($item->fb_charges) : 'N/A'}}</td>
                      <td>
                        <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('transaction.editOPayment', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Customer No</th>
                    <th>Order No</th>
                    <th>Amount</th>
                    <th>Fees</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
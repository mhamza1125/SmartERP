@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Transaction Table</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('expense') }}" class="btn btn-primary">Expense</a>
                <a href="{{ route('ePayment') }}" class="btn btn-primary">Employee</a>
                <a href="{{ route('vPayment') }}" class="btn btn-primary">Vendor</a>
                <a href="{{ route('cPayment') }}" class="btn btn-primary">Contractor</a>
                <a href="{{ route('oPayment') }}" class="btn btn-primary">Customer</a>
                <a href="{{ route('transaction.addTransfer') }}" class="btn btn-success">Bank/Cash Transfer</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            {{-- Date Filter Form --}}
            <form action="{{ route('transaction.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
              @csrf
              <div class="row">
                <div class="form-group col-md-4">
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom ?? ''}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-4">
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto ?? ''}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-4 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                  @if(!empty($dfrom) && !empty($dto))
                    <a href="{{ route('transaction') }}" class="btn btn-secondary mt-2">Clear</a>
                  @endif
                </div>
              </div>
            </form>

            @if(!empty($dfrom) && !empty($dto))
              <div class="alert alert-info">
                <strong>Filtered Results:</strong> Showing transactions from {{ date("d-m-Y", strtotime($dfrom)) }} to {{ date("d-m-Y", strtotime($dto)) }}
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                  @php $index = 1 @endphp
                    @foreach($transaction as $item)
                      @unless($item->transaction_type == 'openingBalance')
                        <tr>
                          <td>{{$index++}}</td>
                          <td>SLE-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                          <td>{{\Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y')}}</td>
                          <td>
                            {{ucfirst($item->transaction_to)}}
                            @if(isset($item->vendor_no)) <br>
                              {{$item->vendor_no}} - {{$item->fname}}
                            @elseif(isset($item->employee_no)) <br>
                              {{$item->employee_no}} - {{$item->name}}
                            @elseif(isset($item->order_no)) <br>
                              {{$item->order_no}} | {{$item->job_no}}
                            @endif
                          </td>
                          <td>{{ucfirst($item->transaction_type)}}</td>
                          <td>{{isset($item->debit) ? number_format($item->debit) : ''}}</td>
                          <td>{{isset($item->credit) ? number_format($item->credit) : ''}}</td>
                          <td>
                            @if($item->transaction_to == 'employee')
                              <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'vendor')
                              <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'contractor')
                              <a href="{{ route('transaction.showCPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'expense')
                              <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'customer')
                              <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'brs')
                              <a href="{{ route('transaction.showBRS', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @else
                              <a href="#" class="btn btn-info btn-sm">None</a>
                            @endif
                          </td>
                        </tr>
                      @endunless
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
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
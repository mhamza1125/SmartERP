@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank Balance Info</h4>
            <div class="card-header-action">
              {{-- <a href="{{ route('stock') }}" class="btn btn-primary">BRS</a> --}}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  {{-- <tr>
                    <th colspan="7"><h6 class="text-center">Available Cash: Rs. {{number_format($balance)}}</h6></th>
                  </tr> --}}
                  <tr>
                    <th>Sr.</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="trow">
                    <td></td>
                    <td><b>Account Title:</b> {{$bank['account_title']}}</td>
                    <td><b>Account No:</b> {{$bank['account']}}</td>
                    <td><b>Bank:</b> {{$bank['hname']}}</td>
                    <td><b>Balance:</b> {{number_format($balance)}}</td>
                    <td></td>
                    <td></td>
                  </tr>
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{ucfirst($item->transaction_to)}}</td>
                      <td>{{ucfirst($item->transaction_type)}}</td>
                      <td>{{isset($item->debit) ? number_format($item->debit) : ''}}</td>
                      <td>{{isset($item->credit) ? number_format($item->credit) : ''}}</td>
                      <td>{{$item->transaction_date}}</td>
                      <td>
                        @if($item->transaction_to == 'employee')
                          <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        @elseif($item->transaction_to == 'vendor')
                          <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        @elseif($item->transaction_to == 'expense')
                          <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                          @elseif($item->transaction_to == 'customer')
                          <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        @else
                          <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View Else</a>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Date</th>
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Detail</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.addOPayment')}}" class="btn btn-primary">Pay</a>
              </div>
            </div>
          </div>
          <div class="card-body row">
            <div class="col md-12">
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <td><b>Customer:</b> {{$customer['customer_no']}} - {{$customer['fname']}} {{$customer['lname']}}</td>
                    <td><b>{{($balance < 0)? 'Receiveable':'Payable'}} Amount:</b> {{number_format(abs($balance))}}</td>
                    {{-- <td><b>Payable Amount:</b> {{number_format($balance)}}</td> --}}
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-12">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($detail->count())
                    @foreach($detail as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>
                        @if(isset($item->transaction_type)) {{ucfirst($item->transaction_type)}}
                        @elseif(isset($item->order_no)) Order - ({{$item->order_no}})
                        @else Unknown Type @endif
                      </td>
                      <td>{{ isset($item->debit) ? number_format($item->debit) : '' }}</td>
                      <td>{{ isset($item->credit) ? number_format($item->credit) : '' }}</td>
                      <td>{{ isset($item->purchase_date) ? $item->purchase_date : (isset($item->transaction_date) ? $item->transaction_date : '') }}</td>
                      <td>
                        @if(isset($item->transaction_type))
                          <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        @elseif(isset($item->order_no))
                        <a href="{{ route('order.show', $item->order_id) }}" class="btn btn-info btn-sm">View</a>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
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
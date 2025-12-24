@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Detail (Customer Currency)</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('customer.detail', $customer['customer_id']) }}" class="btn btn-primary">Ledger (PKR)</a>
                <a href="{{ route('transaction.addOPayment')}}" class="btn btn-primary">Receive</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('customer.filter2', $customer['customer_id']) }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
              <div class="row">
                <div class="form-group col-md-5">                    
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-5">                    
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-2 mt-4">     
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                </div>
              </div>
            </form>
            <div class="row">
              <div class="col md-12">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><b>Customer:</b> {{$customer['customer_no']}} - {{$customer['fname']}} {{$customer['lname']}}</td>
                      <td><b>Currency:</b> {{$customer['cuname'] ?? 'USD'}}</td>
                      @if(!empty($dfrom) && !empty($dto))
                        <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                        <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                      @endif
                      <td><b>{{($ccBalance > 0)? 'Receivable':'Payable'}} Amount:</b> {{number_format(abs($ccBalance), 2)}} {{$customer['cuname'] ?? 'USD'}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Transaction Type</th>
                      <th>Debit ({{$customer['cuname'] ?? 'USD'}})</th>
                      <th>Credit ({{$customer['cuname'] ?? 'USD'}})</th>
                      <th>Balance ({{$customer['cuname'] ?? 'USD'}})</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $index = 1; $ccBalance = $ccOBalance ?? 0; @endphp
                    @if($ccOBalance != 0)
                      <tr>
                        <td>{{$index++}}</td>
                        <td>{{$dfrom}}</td>
                        <td>Opening Balance</td>
                        <td>{{ $ccOBalance > 0 ? number_format(abs($ccOBalance), 2) : '' }}</td>
                        <td>{{ $ccOBalance < 0 ? number_format(abs($ccOBalance), 2) : '' }}</td>
                        <td>{{ number_format($ccOBalance, 2) }}</td>
                        <td></td>
                      </tr>
                    @endif
                    @if($detail->count())
                      @foreach($detail as $item)
                      @php
                        // For customer currency ledger:
                        // - Deliveries: use price2 (customer currency) from order items
                        // - Payments: use cc_amount from transactions
                        $isPayment = isset($item->transaction_type) && $item->transaction_type == 'orderPayment';

                        if ($isPayment) {
                          // Payment: use cc_amount, display in credit column
                          $displayDebit = 0;
                          $displayCredit = $item->cc_amount ?? 0;
                          $ccBalance += $displayCredit; // Payment increases balance (reduces receivable)
                        } else {
                          // Delivery: use price2 (customer currency), display in debit column
                          $displayDebit = $item->price2 ?? 0;
                          $displayCredit = 0;
                          $ccBalance -= $displayDebit; // Delivery decreases balance (increases receivable)
                        }
                      @endphp
                      <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ isset($item->stock_date) ? $item->stock_date : (isset($item->transaction_date) ? $item->transaction_date : '') }}</td>
                        <td>
                          @if(isset($item->transaction_type)) {{ucfirst($item->transaction_type)}}
                          @elseif(isset($item->stock_no)) Delivery - ({{$item->stock_no}})
                          @else Unknown Type @endif
                        </td>
                        <td>{{ $displayDebit > 0 ? number_format($displayDebit, 2) : '' }}</td>
                        <td>{{ $displayCredit > 0 ? number_format($displayCredit, 2) : '' }}</td>
                        <td>{{number_format($ccBalance, 2)}}</td>
                        <td>
                          @if(isset($item->transaction_type))
                            @if($item->transaction_type == 'openingBalance')
                              <a href="#" class="btn btn-info btn-sm">View</a>
                            @else
                              <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @endif
                          @elseif(isset($item->stock_no))
                          <a href="{{ route('delivery.show', $item->delivery_id) }}" class="btn btn-info btn-sm">View</a>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    @endif
                    @if($ccCBalance != 0)
                      <tr>
                        <td>{{$index++}}</td>
                        <td>{{$dto}}</td>
                        <td>Closing Balance</td>
                        <td>{{ $ccCBalance < 0 ? number_format(abs($ccCBalance), 2) : '' }}</td>
                        <td>{{ $ccCBalance > 0 ? number_format(abs($ccCBalance), 2) : '' }}</td>
                        <td>{{ $ccBalance ? number_format($ccBalance += $ccCBalance) : number_format($ccOBalance + $ccCBalance) }}</td>
                        <td></td>
                      </tr>
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Transaction Type</th>
                      <th>Debit ({{$customer['cuname'] ?? 'USD'}})</th>
                      <th>Credit ({{$customer['cuname'] ?? 'USD'}})</th>
                      <th>Balance ({{$customer['cuname'] ?? 'USD'}})</th>
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
  </div>
</section>
@endsection


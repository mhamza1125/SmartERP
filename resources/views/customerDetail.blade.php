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
                <a class="btn btn-info" href="{{ route('customer.ledger.print', $customer['customer_id']) }}{{ !empty($dfrom) && !empty($dto) ? '?dfrom=' . $dfrom . '&dto=' . $dto : '' }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.addOPayment')}}" class="btn btn-primary">Receive</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('customer.filter', $customer['customer_id']) }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
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
                      <td><b>Currency:</b> {{$currencyName}}</td>
                      @if(!empty($dfrom) && !empty($dto))
                        <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                        <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                      @endif
                      <td><b>{{($balance > 0)? 'Receivable':'Payable'}} Amount:</b> {{number_format(abs($balance))}}</td></td>
                      {{-- <td><b>Payable Amount:</b> {{number_format($balance)}}</td> --}}
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
                      <th>Debit</th>
                      <th>Credit</th>
                      <th>Balance</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $index = 1; $balance = $oBalance; @endphp
                    @if($oBalance != 0)
                      <tr>
                        <td>{{$index++}}</td>
                        <td>{{$dfrom}}</td>
                        <td>Opening Balance</td>
                        <td>{{ $oBalance > 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ $oBalance < 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ number_format($oBalance) }}</td>
                        <td></td>
                      </tr>
                    @endif
                    @if($detail->count())
                      @foreach($detail as $item)
                      @php
                        // For customer ledger:
                        // - Deliveries (stored in debit) should display in Debit column
                        // - Payments (stored in debit) should display in Credit column
                        // - For order payments with cc_amount, use cc_amount instead of debit
                        // - For general vouchers (transaction_type = 'generalVoucher'):
                        //   - Use cc_amount for display and balance
                        //   - If credit IS NULL: display cc_amount in Credit column (customer credit)
                        //   - If debit IS NULL: display cc_amount in Debit column (customer charge)
                        $isPayment = isset($item->transaction_type) && $item->transaction_type == 'orderPayment';
                        $isGeneralVoucher = isset($item->transaction_type) && $item->transaction_type == 'generalVoucher';

                        if ($isGeneralVoucher && !empty($item->cc_amount)) {
                          // General Voucher: stored in cc_amount
                          // Determine column by strict NULL check:
                          // - If credit is NULL: display cc_amount in Credit column (customer credit)
                          // - If debit is NULL: display cc_amount in Debit column (customer charge)
                          if (is_null($item->credit)) {
                            // Credit Voucher (Credit to Customer) - credit IS NULL
                            $displayDebit = 0;
                            $displayCredit = $item->cc_amount;
                            $balance += $item->cc_amount; // Decreases customer liability (increases balance)
                          } else if (is_null($item->debit)) {
                            // Debit Voucher (Charge to Customer) - debit IS NULL
                            $displayDebit = $item->cc_amount;
                            $displayCredit = 0;
                            $balance -= $item->cc_amount; // Increases customer liability (reduces balance)
                          } else {
                            // Fallback: both have values, shouldn't happen but handle gracefully
                            $displayDebit = 0;
                            $displayCredit = 0;
                          }
                        } elseif ($isPayment) {
                          // Payment: stored in debit, but display in credit column
                          // Use cc_amount if available (customer currency), otherwise use debit (PKR)
                          $displayDebit = 0;
                          $displayCredit = !empty($item->cc_amount) ? $item->cc_amount : ($item->debit ?? 0);
                          // For balance calculation, use cc_amount if available
                          $amountToUse = !empty($item->cc_amount) ? $item->cc_amount : ($item->debit ?? 0);
                          $balance += $amountToUse; // Payment increases balance (reduces receivable)
                        } else {
                          // Delivery: stored in debit, display in debit column
                          $displayDebit = $item->debit ?? 0;
                          $displayCredit = $item->credit ?? 0;
                          isset($item->debit) ? $balance -= $item->debit : '';
                          isset($item->credit) ? $balance += $item->credit : '';
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
                        <td>{{ $displayDebit > 0 ? number_format($displayDebit) : '' }}</td>
                        <td>{{ $displayCredit > 0 ? number_format($displayCredit) : '' }}</td>
                        <td>{{number_format($balance)}}</td>
                        <td>
                          @if(isset($item->transaction_type))
                            @if($item->transaction_type == 'openingBalance')
                              <a href="#" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_type == 'generalVoucher')
                              <a href="{{ route('transaction.showGeneralVoucher', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
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
                    @if($cBalance != 0)
                      <tr>
                        <td>{{$index++}}</td>
                        <td>{{$dto}}</td>
                        <td>Closing Balance</td>
                        <td>{{ $cBalance < 0 ? number_format(abs($cBalance)) : '' }}</td>
                        <td>{{ $cBalance > 0 ? number_format(abs($cBalance)) : '' }}</td>
                        <td>{{ $balance ? number_format($balance += $cBalance) : number_format($oBalance + $cBalance) }}</td>
                        <td></td>
                      </tr>
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Transaction Type</th>
                      <th>Debit</th>
                      <th>Credit</th>
                      <th>Balance</th>
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

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
                        isset($item->debit) ? $balance -= $item->debit : '';
                        isset($item->credit) ? $balance += $item->credit : '';
                        // For customer ledger, display DB debit in Debit column and DB credit in Credit column (no reversal)
                        $displayDebit = $item->debit ?? 0;
                        $displayCredit = $item->credit ?? 0;
                      @endphp
                      <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ isset($item->order_date) ? $item->order_date : (isset($item->transaction_date) ? $item->transaction_date : '') }}</td>
                        <td>
                          @if(isset($item->transaction_type)) {{ucfirst($item->transaction_type)}}
                          @elseif(isset($item->order_no)) Order - ({{$item->job_no}})
                          @else Unknown Type @endif
                        </td>
                        <td>{{ $displayDebit > 0 ? number_format($displayDebit) : '' }}</td>
                        <td>{{ $displayCredit > 0 ? number_format($displayCredit) : '' }}</td>
                        <td>{{number_format($balance)}}</td>
                        <td>
                          @if(isset($item->transaction_type))
                            @if($item->transaction_type == 'openingBalance')
                              <a href="#" class="btn btn-info btn-sm">View</a>
                            @else
                              <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @endif
                          @elseif(isset($item->order_no))
                          <a href="{{ route('order.show', $item->order_id) }}" class="btn btn-info btn-sm">View</a>
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
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
              @can('create', App\Models\Transaction::class)<a href="{{ route('transaction.addBRS') }}" class="btn btn-primary">Balance Adjustment</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('bankBalance.filter', $bank['bank_id']) }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
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
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  @if(!empty($dfrom) && !empty($dto))
                    <tr>
                      <th colspan="2"></th>
                      <th colspan="3"><b>Date From:</b> {{date("d-m-Y", strtotime($dfrom))}}</th>
                      <th colspan="3"><b>Date To:</b> {{date("d-m-Y", strtotime($dto))}}</th>
                    </tr>
                  @endif
                  <tr>
                    <th></th>
                    <th colspan="2"><b>Account Title:</b> {{$bank['account_title']}}</th>
                    <th colspan="2"><b>Account No:</b> {{$bank['account']}}</th>
                    <th><b>Bank:</b> {{$bank['hname']}}</th>
                    <th><b>Balance:</b> {{number_format($balance)}}</th>
                    <th></th>
                  </tr>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Transaction</th>
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
                      <td>{{\Carbon\Carbon::parse($dfrom)->format('d-m-Y')}}</td>
                      <td>Opening Balance</td>
                      <td>Opening Balance</td>
                      <td>{{ $oBalance < 0 ? number_format(abs($oBalance)) : '' }}</td>
                      <td>{{ $oBalance > 0 ? number_format(abs($oBalance)) : '' }}</td>
                      <td>{{ number_format($oBalance) }}</td>
                      <td></td>
                    </tr>
                  @endif
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    @php
                      $balance += $item->debit;
                      $balance -= $item->credit;
                      // For bank ledger, display as-is (debit=inflow, credit=outflow)
                      $displayDebit = $item->debit;
                      $displayDC = $item->db_charges;
                      $displayCredit = $item->credit;
                    @endphp
                    <tr>
                      <td>{{$index++}}</td>
                      <td>{{\Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y')}}</td>
                      <td>{{transactionLabel($item->transaction_to)}}</td>
                      <td>{{transactionLabel($item->transaction_type)}}</td>
                      <td>{{isset($displayDebit) ? number_format($displayDebit) : ''}} ({{$displayDC}})</td>
                      <td>{{isset($displayCredit) ? number_format($displayCredit) : ''}}</td>
                      <td>{{number_format($balance)}}</td>
                      <td>
                        @include('transactionViewButton')
                      </td>
                    </tr>
                    @endforeach
                  @endif
                  @if($cBalance != 0)
                    <tr>
                      <td>{{$index++}}</td>
                      <td>{{\Carbon\Carbon::parse($dto)->format('d-m-Y')}}</td>
                      <td>Closing Balance</td>
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
                    <th>Transaction</th>
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
</section>
@endsection
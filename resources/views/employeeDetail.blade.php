@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Employee Detail</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.addEPayment')}}" class="btn btn-primary">Pay</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('employee.filter', $employee['employee_id']) }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
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
                      <td><b>Employee:</b> {{$employee['employee_no']}} - {{$employee['name']}}</td>
                      @if(!empty($dfrom) && !empty($dto))
                        <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                        <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                      @endif
                      {{-- <td><b>Department:</b> {{$employee['dname']}} </td> --}}
                      {{-- <td><b>Contact:</b> {{$employee['phone']}}</td> --}}
                      <td><b>{{($balance > 0)? 'Payable':'Receivable'}} Amount:</b> {{number_format(abs($balance))}}</td>
                      {{-- <td><b>Receivable Amount:</b> {{number_format($balance)}}</td> --}}
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
                        <td>{{ $oBalance < 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ $oBalance > 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ number_format($oBalance) }}</td>
                        <td></td>
                      </tr>
                    @endif
                    @if($detail->count())
                      @foreach($detail as $item)
                      @php
                        if (isset($item->transaction_type) && in_array($item->transaction_type, ['advance', 'receiveAdvance', 'openingBalance'])) {
                          isset($item->debit) ? $balance -= $item->debit : ''; 
                          isset($item->credit) ? $balance += $item->credit : ''; 
                        }
                      @endphp
                      <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ isset($item->purchase_date) ? $item->purchase_date : (isset($item->transaction_date) ? $item->transaction_date : '') }}</td>
                        <td>{{ isset($item->transaction_type) ? ucfirst($item->transaction_type) : 'Purchase Order' }}</td>
                        <td>{{ isset($item->debit) ? number_format($item->debit) : '' }}</td>
                        <td>{{ isset($item->credit) ? number_format($item->credit) : '' }}</td>
                        <td>{{number_format($balance)}}</td>
                        <td>
                          @if($item->transaction_type == 'openingBalance')
                            <a href="#" class="btn btn-info btn-sm">View</a>
                          @else
                            <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
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
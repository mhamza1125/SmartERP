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
                <a class="btn btn-info" href="{{ route('employee.ledger.print', $employee['employee_id']) }}{{ !empty($dfrom) && !empty($dto) ? '?dfrom=' . $dfrom . '&dto=' . $dto : '' }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
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
                        <td>{{ $oBalance > 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ $oBalance < 0 ? number_format(abs($oBalance)) : '' }}</td>
                        <td>{{ number_format($oBalance) }}</td>
                        <td></td>
                      </tr>
                    @endif
                    @if($detail->count())
                      @foreach($detail as $item)
                      @php
                        // For employee ledger (liability account):
                        // DB debit (displayed as Credit) = work done, increases liability = ADD to balance
                        // DB credit (displayed as Debit) = payments made, decreases liability = SUBTRACT from balance
                        // Include ALL transaction types - no filtering
                        $debit = $item->debit ?? 0;
                        $credit = $item->credit ?? 0;
                        $balance += $debit - $credit;

                        // For employee ledger, reverse the display (DB debit shown in credit column, DB credit shown in debit column)
                        $displayDebit = $credit;
                        $displayCredit = $debit;
                      @endphp
                      <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ isset($item->purchase_date) ? $item->purchase_date : (isset($item->transaction_date) ? $item->transaction_date : '') }}</td>
                        <td>
                          @if(isset($item->transaction_type))
                            @switch($item->transaction_type)
                              @case('advance')
                                Give Loan
                                @break
                              @case('receiveAdvance')
                                Receive Loan Repayment
                                @break
                              @case('salaryAdvance')
                                Salary Advance
                                @break
                              @case('salary')
                                Monthly Salary
                                @if(isset($item->description))
                                  <br><small class="text-muted">{{ $item->description }}</small>
                                @endif
                                @break
                              @default
                                {{ ucfirst($item->transaction_type) }}
                            @endswitch
                          @else
                            Purchase Order
                          @endif
                        </td>
                        <td>{{ $displayDebit > 0 ? number_format($displayDebit) : '' }}</td>
                        <td>{{ $displayCredit > 0 ? number_format($displayCredit) : '' }}</td>
                        <td>{{number_format($balance)}}</td>
                        <td>
                          @if($item->transaction_type == 'openingBalance')
                            <a href="#" class="btn btn-info btn-sm">View</a>
                          @elseif($item->transaction_type == 'wages')
                            <div>
                              <span class="badge badge-success mb-1">{{ $item->stock_no ?? 'N/A' }}</span><br>
                              <small class="text-muted">
                                <strong>{{ $item->item_count ?? 1 }} items</strong> - Total Wages: {{ number_format($item->debit) }}
                              </small><br>
                              <div class="btn-group mt-1">
                                @if(isset($item->stock_id))
                                  <a href="{{ route('stock.show', $item->stock_id) }}" class="btn btn-info btn-xs">View</a>
                                @endif
                                @if(isset($item->wage_details) && !empty($item->wage_details) && isset($item->stock_id))
                                  <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#wagesModal{{ $item->stock_id }}">
                                    <i class="fas fa-info-circle"></i> Info
                                  </button>
                                @endif
                              </div>
                            </div>
                          @elseif($item->transaction_type == 'salary')
                            <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
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

{{-- Wages Detail Modals --}}
@if($detail->count())
  @foreach($detail as $item)
    @if(isset($item->transaction_type) && $item->transaction_type == 'wages' && isset($item->wage_details))
      <div class="modal fade" id="wagesModal{{ $item->stock_id }}" tabindex="-1" role="dialog" aria-labelledby="wagesModalLabel{{ $item->stock_id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="wagesModalLabel{{ $item->stock_id }}">
                Wages Details - {{ $item->stock_no }}
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Size</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                      <th>Wages (PKR)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $totalWages = 0; @endphp
                    @foreach($item->wage_details as $index => $detail)
                      @php $totalWages += $detail['wages']; @endphp
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail['article_no'] }}</td>
                        <td>{{ $detail['size_name'] }}</td>
                        <td>{{ $detail['stage_name'] }}</td>
                        <td>{{ $detail['quantity'] }}</td>
                        <td>{{ number_format($detail['wages']) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="table-info">
                      <th colspan="5">Total Wages:</th>
                      <th>{{ number_format($totalWages) }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              @if(isset($item->stock_id))
                <a href="{{ route('stock.show', $item->stock_id) }}" class="btn btn-info">View Full Issuance</a>
              @endif
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach
@endif

@endsection
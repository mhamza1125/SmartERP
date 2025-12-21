@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Purchase Ledger</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('purchase.ledger.print') }}{{ !empty($dfrom) && !empty($dto) ? '?dfrom=' . $dfrom . '&dto=' . $dto : '' }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('purchase') }}" class="btn btn-primary">Back</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('purchase.ledger.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
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
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                </div>
              </div>
            </form>

            {{-- Summary Info --}}
            @php
              // Calculate the final balance for the summary
              $summaryBalance = $oBalance;
              foreach($detail as $item) {
                $debit = $item->debit ?? 0;
                $credit = $item->credit ?? 0;
                $summaryBalance += $debit - $credit;
              }
            @endphp
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><b>Report Type:</b> Purchase Ledger</td>
                      @if(!empty($dfrom) && !empty($dto))
                        <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                        <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                      @endif
                      <td><b>Payable Amount:</b> {{number_format(abs($summaryBalance), 2)}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            {{-- Ledger Table --}}
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Reference No</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $index = 1;
                    $balance = $oBalance;
                    $totalDebit = 0;
                    $totalCredit = 0;
                  @endphp
                  @if($oBalance != 0)
                    <tr style="background-color: #f9f9f9;">
                      <td>{{$index++}}</td>
                      <td>{{$dfrom ?? 'Start'}}</td>
                      <td>-</td>
                      <td><strong>Opening Balance</strong></td>
                      <td class="text-right">{{ $oBalance > 0 ? number_format($oBalance, 2) : '-' }}</td>
                      <td class="text-right">{{ $oBalance < 0 ? number_format(abs($oBalance), 2) : '-' }}</td>
                      <td class="text-right"><strong>{{ number_format($balance, 2) }}</strong></td>
                      <td></td>
                    </tr>
                  @endif
                  @if($detail->count())
                    @foreach($detail as $item)
                      @php
                        $debit = $item->debit ?? 0;
                        $credit = $item->credit ?? 0;
                        $totalDebit += $debit;
                        $totalCredit += $credit;
                        $balance += $debit - $credit;

                        // Determine reference number and description
                        $refNo = '-';
                        $description = 'Transaction';

                        if(isset($item->purchase_no)) {
                          // This is a purchase
                          $refNo = $item->purchase_no;
                          $description = 'Purchase';
                        } elseif(isset($item->return_no)) {
                          // This is a return
                          $refNo = $item->return_no;
                          $description = 'Return';
                        } elseif(isset($item->transaction_id)) {
                          // This is a vendor payment
                          $refNo = 'TXN-' . date('Y') . '-' . str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT);
                          $description = $item->description ?? 'Vendor Payment';
                        }
                      @endphp
                      <tr>
                        <td>{{$index++}}</td>
                        <td>{{ isset($item->purchase_date) ? $item->purchase_date : (isset($item->return_date) ? $item->return_date : ($item->transaction_date ?? 'N/A')) }}</td>
                        <td>{{ $refNo }}</td>
                        <td>{{ strip_tags($description) }}</td>
                        <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                        <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                        <td class="text-right">{{ number_format($balance, 2) }}</td>
                        <td>
                          @if(isset($item->purchase_id))
                            <a href="{{ route('purchase.show', $item->purchase_id) }}" class="btn btn-info btn-sm">View</a>
                          @elseif(isset($item->return_id))
                            <a href="{{ route('return.show', $item->return_id) }}" class="btn btn-info btn-sm">View</a>
                          @elseif(isset($item->transaction_id))
                            <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <th colspan="4" class="text-right">Total:</th>
                    <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                    <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
                    <th class="text-right">{{ number_format($balance, 2) }}</th>
                    <th></th>
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


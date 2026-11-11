@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank/Cash Transfer Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('transfer.print', $transaction['transaction_id']) }}" class="btn btn-info" target="_blank">
                  <i class="fas fa-print"></i> Print
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.editTransfer', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Transfer From:</b> {{ $info->details['From Account'] ?? (($transaction['bank_id'] ?? 0) == 0 ? 'Cash' : $transaction['bname'] . ' - ' . $transaction['account_title']) }}</td></tr>
                    <tr><td><b>Transfer To:</b> {{ $info->details['To Account'] ?? '-' }}</td></tr>
                    <tr><td><b>Transfer Type:</b> Bank/Cash Transfer</td></tr>
                    @if(!empty($info->cheque_no))
                      <tr><td><b>Cheque No:</b> {{ $info->cheque_no }}</td></tr>
                    @endif
                    @if(!empty($info->note))
                      <tr><td><b>Description:</b> {{ $info->note }}</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Voucher No:</b> SLE-{{ date('Y') }}-{{ str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                    @php
                      $transferAmount = $transaction['credit'] ?? $transaction['debit'] ?? 0;
                    @endphp
                    <tr><td><b>Amount:</b> {{number_format($transferAmount)}}</td></tr>
                    <tr><td><b>Amount in Words:</b> {{ numberToWordsWithCurrency($transferAmount) }}</td></tr>
                    <tr><td><b>Transfer Date:</b> {{\Carbon\Carbon::parse($transaction['transaction_date'])->format('d-m-Y')}}</td></tr>
                  </tbody>
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


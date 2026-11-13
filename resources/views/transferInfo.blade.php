@extends('index')
@section('content')
@php
  $transferAmount = (float) ($transaction['credit'] ?? 0) ?: (float) ($transaction['debit'] ?? 0);
  $thisAccount = ($transaction['bank_id'] ?? 0) == 0 ? 'Cash' : trim(($transaction['bname'] ?? '') . ' - ' . ($transaction['account_title'] ?? ''), ' -');
@endphp
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
            @include('transactionVoucherInfo', [
              'accountRows' => array_filter([
                'Transfer From' => $info->details['From Account'] ?? ($transaction['credit'] ? $thisAccount : null),
                'Transfer To' => $info->details['To Account'] ?? ($transaction['debit'] ? $thisAccount : null),
                'Cheque No' => $info->cheque_no ?? null,
              ]),
              'voucherRows' => ['Voucher Type' => 'Bank/Cash Transfer'],
              'amount' => $transferAmount,
              'description' => e($info->note ?? $transaction['description'] ?? ''), // plain text, shown escaped
            ])
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

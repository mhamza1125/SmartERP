@extends('index')
@section('content')
@php
  // A balance adjustment is one entry: debit raises the account balance, credit lowers it
  $debit = (float) ($transaction['debit'] ?? 0);
  $credit = (float) ($transaction['credit'] ?? 0);
  $adjustmentAmount = $debit > 0 ? $debit : $credit;
  $adjustmentType = $debit > 0 ? 'Increase Balance' : ($credit > 0 ? 'Decrease Balance' : '-');
  $isOpening = ($transaction['transaction_to'] ?? '') === 'admin';
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ $isOpening ? 'Opening Balance Info' : 'Balance Adjustment (BRS) Info' }}</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('brs.print', $transaction['transaction_id']) }}" target="_blank">
                  <i class="fas fa-print"></i> Print
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                @unless($isOpening)
                  @if(auth()->user()->can('edit', App\Models\Transaction::class))<a href="{{ route('transaction.editBRS', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>@endif
                @endunless
              </div>
            </div>
          </div>
          <div class="card-body">
            @include('transactionVoucherInfo', [
              'accountRows' => $transaction['bank_id']
                ? ['Adjustment Of' => 'Bank Balance', 'Bank' => $transaction['bname'], 'Account Title' => $transaction['account_title'], 'Account No' => $transaction['account']]
                : ['Adjustment Of' => 'Petty Cash'],
              'voucherRows' => [
                'Voucher Type' => $isOpening ? 'Opening Balance' : 'Balance Adjustment (BRS)',
                'Adjustment' => $adjustmentType,
              ],
              'amount' => $adjustmentAmount,
              'description' => $transaction['description'],
            ])
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

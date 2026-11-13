{{--
    Shared body of the Balance Adjustment (BRS) and Bank/Cash Transfer view pages:
    accounts on the left, voucher facts on the right, description below.
    $accountRows, $voucherRows: label => value; $amount; $description (may hold HTML from the editor)
--}}
<div class="row">
  <div class="col-md-7">
    <table class="table table-sm">
      <tbody>
        @foreach($accountRows as $label => $value)
          <tr><td><b>{{ $label }}:</b> {{ $value !== null && $value !== '' ? $value : '-' }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="col-md-5">
    <table class="table table-sm">
      <tbody>
        <tr><td><b>Voucher No:</b> SLE-{{ date('Y') }}-{{ str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
        @foreach($voucherRows as $label => $value)
          <tr><td><b>{{ $label }}:</b> {{ $value }}</td></tr>
        @endforeach
        <tr><td><b>Date:</b> {{ formatDate($transaction['transaction_date'] ?? null, '-') }}</td></tr>
        <tr><td><b>Amount:</b> {{ number_format($amount, 2) }}</td></tr>
        <tr><td><b>Amount in Words:</b> {{ numberToWordsWithCurrency($amount) }}</td></tr>
      </tbody>
    </table>
  </div>
</div>
@if(trim(strip_tags((string) $description)) !== '')
  <div class="row">
    <div class="col-12">
      <table class="table table-sm mb-0">
        <tbody>
          <tr><td><b>Description:</b></td></tr>
          <tr><td>{!! $description !!}</td></tr>
        </tbody>
      </table>
    </div>
  </div>
@endif

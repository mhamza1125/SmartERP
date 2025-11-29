@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>BRS Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('brs.print', $transaction['transaction_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.editBRS', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    @if($transaction['bank_id'])
                      <tr><td><b>BRS Of:</b> Bank Balance</td></tr>
                      <tr><td><b>Bank:</b> {{$transaction['bname']}}</td></tr>
                      <tr><td><b>Account Title:</b> {{$transaction['account_title']}}</td></tr>
                      <tr><td><b>Account No:</b> {{$transaction['account']}}</td></tr>
                    @else
                      <tr><td><b>BRS Of:</b> Petty Cash</td></tr>
                    @endif
                    
                    @if($transaction['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td colspan="3">@php echo $transaction['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Voucher No:</b> SSL-{{ date('Y') }}-{{ str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                    @php
                      // Properly handle null and zero values in both debit and credit columns
                      $brsAmount = 0;
                      $brsType = 'Unknown';

                      if (isset($transaction['debit']) && $transaction['debit'] > 0) {
                          $brsAmount = $transaction['debit'];
                          $brsType = 'Increase Balance';
                      } elseif (isset($transaction['credit']) && $transaction['credit'] > 0) {
                          $brsAmount = $transaction['credit'];
                          $brsType = 'Decrease Balance';
                      }
                    @endphp
                    <tr><td><b>BRS Type:</b> {{ $brsType }}</td></tr>
                    <tr><td><b>Amount:</b> {{number_format($brsAmount)}}</td></tr>
                    <tr><td><b>Amount in Words:</b> {{ numberToWordsWithCurrency($brsAmount) }}</td></tr>
                    <tr><td><b>BRS Date:</b> {{$transaction['transaction_date']}}</td></tr>
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
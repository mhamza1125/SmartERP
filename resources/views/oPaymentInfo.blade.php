@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Payment Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('payment.print', $transaction['transaction_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('oPayment') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.editOPayment', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Voucher No:</b> SLE-{{ date('Y') }}-{{ str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                    @if($transaction['bank_id'])
                    <tr><td><b>Received By:</b> {{$transaction['bname']}}</td></tr>
                    <tr><td><b>Account Title:</b> {{$transaction['account_title']}}</td></tr>
                    <tr><td><b>Account No:</b> {{$transaction['account']}}</td></tr>
                    @else
                      <tr><td><b>Received By:</b> Cash Payment</td></tr>
                    @endif
                    @php
                      // Properly handle null and zero values in both debit and credit columns
                      $grossAmount = 0;
                      if (isset($transaction['debit']) && $transaction['debit'] > 0) {
                          $grossAmount = $transaction['debit'];
                      } elseif (isset($transaction['credit']) && $transaction['credit'] > 0) {
                          $grossAmount = $transaction['credit'];
                      }
                      $ccAmount = $transaction['cc_amount'] ?? 0;
                      $fbCharges = $transaction['fb_charges'] ?? 0;
                      $dbCharges = $transaction['db_charges'] ?? 0;
                      $currencyName = $transaction['cname'] ?? 'PKR';
                    @endphp
                    <tr><td><b>Amount Received (PKR):</b> {{number_format($grossAmount, 2)}}</td></tr>
                    @if($ccAmount > 0)
                    <tr><td><b>CC Amount ({{ $currencyName }}):</b> {{number_format($ccAmount, 2)}}</td></tr>
                    @endif
                    @if($fbCharges > 0)
                    <tr><td><b>Foreign Bank Charges ({{ $currencyName }}):</b> {{number_format($fbCharges, 2)}}</td></tr>
                    @endif
                    @if($dbCharges > 0)
                    <tr><td><b>Domestic Bank Charges (PKR):</b> {{number_format($dbCharges, 2)}}</td></tr>
                    @endif
                    <tr><td><b>Amount in Words:</b> {{ numberToWordsWithCurrency($grossAmount) }}</td></tr>
                    @if($transaction['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td colspan="3">@php echo $transaction['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer:</b> {{$transaction['customer_no']}} - {{$transaction['fname']}}</td></tr>
                    <tr><td><b>Pay Date:</b> {{\Carbon\Carbon::parse($transaction['transaction_date'])->format('d-m-Y')}}</td></tr>
                    <tr><td><b>Order No:</b> {{$transaction['order_no']}}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                @if($image->count())
                  <h5>Images</h5>
                  <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                    @foreach($image as $item)
                      <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                        <a href="{{ URL::asset('resources/transaction/'. $item->image) }}">
                          <img class="img-responsive thumbnail" src="{{ URL::asset('resources/transaction/'. $item->image) }}" alt="">
                        </a>
                        <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'transaction'])}}" method="POST">
                          @csrf
                          <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                        </form>
                      </div>
                    @endforeach
                  </div>
                @else
                  <blockquote> No Images </blockquote>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
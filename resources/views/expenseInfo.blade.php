@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Expense Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('expense.print', $transaction['transaction_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('expense') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.editExpense', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                      <tr><td><b>Voucher No:</b> TXN-{{ date('Y') }}-{{ str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                      @if($transaction['bank_id'])
                        <tr><td><b>Paid By:</b> {{$transaction['bname']}}</td></tr>
                        <tr><td><b>Account Title:</b> {{$transaction['account_title']}}</td></tr>
                        <tr><td><b>Account No:</b> {{$transaction['account']}}</td></tr>
                      @else
                        <tr><td><b>Paid By:</b> Cash Payment</td></tr>
                      @endif
                      @php
                        // For expense transactions, the amount is stored in credit column (cash outflow)
                        $expenseAmount = $transaction['credit'] ?? $transaction['debit'] ?? 0;
                      @endphp
                      <tr><td><b>Amount:</b> {{number_format($expenseAmount)}}</td></tr>
                      <tr><td><b>Amount in Words:</b> {{ numberToWordsWithCurrency($expenseAmount) }}</td></tr>
                    @if($transaction['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td colspan="3">@php echo $transaction['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Expense Head:</b> {{$transaction['hname']}}</td></tr>
                    <tr><td><b>Expense Date:</b> {{$transaction['transaction_date']}}</td></tr>
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
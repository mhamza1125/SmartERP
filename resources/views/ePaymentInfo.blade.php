@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Employee Payment Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('ePayment') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('transaction.editEPayment', $transaction['transaction_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                      @if($transaction['bank_id'])
                        <tr><td><b>Paid By:</b> {{$transaction['bname']}}</td></tr>
                        <tr><td><b>Account Title:</b> {{$transaction['account_title']}}</td></tr>
                        <tr><td><b>Account No:</b> {{$transaction['account']}}</td></tr>
                      @else
                        <tr><td><b>Paid By:</b> Cash Payment</td></tr>
                      @endif
                      <tr><td><b>Amount:</b> {{number_format($transaction['debit'] ?? $transaction['credit'])}}</td></tr>
                    @if($transaction['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td colspan="3">@php echo $transaction['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Employee:</b> {{$transaction['employee_no']}} - {{$transaction['name']}}</td></tr>
                    <tr><td><b>Pay Date:</b> {{$transaction['transaction_date']}}</td></tr>
                    <tr><td><b>Payment Type:</b> {{ucfirst($transaction['transaction_type'])}}</td></tr>
                      @if($transaction['payee_bank_id'])
                      <tr>
                        <tr><td><b>Received By:</b> {{$transaction['rname']}}</td></tr>
                        <tr><td><b>Account Title:</b> {{$transaction['raccount_title']}}</td></tr>
                        <tr><td><b>Account No:</b> {{$transaction['raccount']}}</td></tr>
                      </tr>
                      @else
                        <tr><td><b>Received By:</b> Cash Payment</td></tr>
                      @endif
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
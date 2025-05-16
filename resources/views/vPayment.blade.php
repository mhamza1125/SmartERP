@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            {{-- Vendor / Contractor Payment Table --}}
            <h4>Vendor Payment Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addVPayment') }}" class="btn btn-primary">Add Payment</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                  @php $index = 1 @endphp
                    @foreach($transaction as $item)
                      @unless($item->transaction_type == 'openingBalance')
                        <tr>
                          <td>{{$index++}}</td>
                          <td>{{$item->transaction_date}}</td>
                          <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                          <td>{{ucfirst($item->transaction_type)}}</td>
                          <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>                  
                          <td>
                            <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('transaction.editVPayment', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                          </td>
                        </tr>
                      @endunless
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
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
</section>
@endsection
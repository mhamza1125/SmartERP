@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Status</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('purchase.add')}}" class="btn btn-primary" target="_blank">Purchase</a>
                <a href="{{ route('delivery.add', $order['order_id']) }}" class="btn btn-primary">Deliver</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer No:</b> {{$order['customer_no']}}</td></tr>
                    <tr><td><b>Customer Name:</b> {{$order['fname']}} {{$order['lname']}}</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">                    
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Size</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($stock->count())
                      @php $product_id = 0; $size = 0; @endphp
                      @foreach($stock as $item)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        @if($item->product_id == $product_id)
                            <td colspan="2"></td>
                        @else
                          <td>{{$item->article_no}}</td>
                          <td>{{$item->name}}</td>
                        @endif
                        @if($item->sname == $size)
                          <td></td>
                        @else
                          <td>{{$item->sname}}</td>
                        @endif
                        <td>{{$item->stname}}</td>
                        <td>{{number_format($item->stockIn - $item->stockOut)}} {{$item->uname}}</td>                  
                      </tr>
                      @php $product_id = $item->product_id; $size = $item->sname @endphp
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Item / Product</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
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
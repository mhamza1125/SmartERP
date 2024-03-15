@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Info</h4>
            <div class="card-header-action">
              <a href="{{ route('order.edit', $order['order_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body row">
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
            <div class="col-md-12 mt-2">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Article</th>
                    <th>Item / Product</th>
                    <th>Size</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  @if($orderItem->count())
                    @php $product_id = 0; @endphp
                    @foreach($orderItem as $item)
                      <tr>
                        @if($item->product_id == $product_id)
                          <td>{{$loop->index + 1}}</td>
                          <td colspan="2"></td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->uname}}</td>
                          <td>{{$item->quantity}}</td>
                          <td>{{$item->price}}</td>
                          <td>{{$item->quantity * $item->price}}</td>
                        @else
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->article_no}}</td>
                          <td>{{$item->name}}</td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->uname}}</td>
                          <td>{{$item->quantity}}</td>
                          <td>{{$item->price}}</td>
                          <td>{{$item->quantity * $item->price}}</td>
                        @endif
                      </tr>
                    @php $product_id = $item->product_id; @endphp
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  @php $total = $orderItem->sum(function($item) {
                    return $item->quantity * $item->price;
                  }); @endphp
                  <tr>
                    <th colspan="4"></th>
                    <th colspan="2">Grand Total:</th>
                    <th>{{ $total }}</th>
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
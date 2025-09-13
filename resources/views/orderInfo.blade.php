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
              <div class="btn-group">
                <a href="{{ route('order.estimate', $order['order_id']) }}" class="btn btn-success">Estimate Material</a>
                <a href="{{ route('order.status', $order['order_id']) }}" class="btn btn-success">Order Status</a>
                <a href="{{ route('delivery.add', $order['order_id']) }}" class="btn btn-success">Deliver</a>
              </div>
              <div class="btn-group">
                <a href="{{ route('order') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('order.edit', $order['order_id']) }}" class="btn btn-primary">Edit</a>
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
                    {{-- @if($order['expected_delivery_date'])
                    <tr><td><b>Expected Delivery:</b> {{$order['expected_delivery_date']}}</td></tr>
                    @endif --}}
                    <tr><td><b>Order Status:</b>
                      @if($order['order_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Processing</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-warning">On Hold</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-success">Partially Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-success">Delivered</span>
                      @elseif($order['order_status'] == 6) <span class="badge badge-success">Completed</span>
                      @elseif($order['order_status'] == 7) <span class="badge badge-danger">Canceled</span>
                      @elseif($order['order_status'] == 8) <span class="badge badge-danger">Returned</span>
                      @elseif($order['delivery_status'] == 9) <span class="badge badge-warning">Disputed</span>
                      @else @endif
                    </td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Product Stage</th>
                      <th>Size</th>
                      <th>Unit</th>
                      <th>Quantity</th>
                      <th>Price (Currency)</th>
                      <th>Exchange (Pkr)</th>
                      <th>Price (Pkr)</th>
                      <th>Total (Pkr)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($orderItem->count())
                      @php $product_id = 0; @endphp
                      @foreach($orderItem as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          @if($item->product_id == $product_id)
                            <td colspan="2"></td>
                          @else
                            <td>{{$item->article_no}}</td>
                            <td>{{$item->name}}</td>
                          @endif                          
                          <td>{{$item->sname}}</td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->uname}}</td>
                          <td>{{$item->quantity}}</td>
                          <td>{{$item->price2}} {{$item->cname}}</td>
                          <td>{{$item->exchange}}</td>
                          <td>{{$item->price}}</td>
                          <td>{{$item->quantity * $item->price}}</td>
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
                      <th colspan="9"></th>
                      <th>Grand Total:</th>
                      <th>{{ number_format($total) }}</th>
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
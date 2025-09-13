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
                    <tr><td><b>Order Status:</b> 
                      @if($order['order_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Processing</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-warning">On Hold</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-success">Partially Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-success">Delivered</span>
                      @elseif($order['order_status'] == 6) <span class="badge badge-success">Completed</span>
                      @elseif($order['order_status'] == 7) <span class="badge badge-danger">Canceled</span>
                      @elseif($order['order_status'] == 8) <span class="badge badge-danger">Returned</span>
                      @elseif($order['order_status'] == 9) <span class="badge badge-warning">Disputed</span>
                      @else @endif
                    </td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="order-tab" data-toggle="tab" href="#order" role="tab" aria-controls="order" aria-selected="true">Order Details</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="dummy-tab" data-toggle="tab" href="#dummy" role="tab" aria-controls="dummy" aria-selected="false">Additional Details</a>
                  </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                  {{-- Order Details Tab --}}
                  <div class="tab-pane fade show active" id="order" role="tabpanel" aria-labelledby="order-tab">
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
                            @if($item->sname == $size && $item->product_id == $product_id)
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
                  {{-- Dummy Table Tab --}}
                  <div class="tab-pane fade" id="dummy" role="tabpanel" aria-labelledby="dummy-tab">
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
                        <tr>
                          <td>1</td>
                          <td>ART-001</td>
                          <td>Sample Product A</td>
                          <td>Medium</td>
                          <td>Production</td>
                          <td>100 Units</td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>ART-002</td>
                          <td>Sample Product B</td>
                          <td>Large</td>
                          <td>Quality Check</td>
                          <td>50 Units</td>
                        </tr>
                        <tr>
                          <td>3</td>
                          <td>ART-003</td>
                          <td>Sample Product C</td>
                          <td>Small</td>
                          <td>Packaging</td>
                          <td>200 Units</td>
                        </tr>
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
    </div>
  </div>
</section>
@endsection
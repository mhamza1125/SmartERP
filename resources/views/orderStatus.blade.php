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
                <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-print"></i> Print
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0)" onclick="printPage('Order Status')">Print Page</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="printTab('order', 'Order Details')">Print Order Details</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="printTab('dummy', 'Additional Details')">Print Additional Details</a>
                  </div>
                </div>
              </div>
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
                  {{-- Additional Details Tab - Dynamic Data --}}
                  <div class="tab-pane fade" id="dummy" role="tabpanel" aria-labelledby="dummy-tab">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Size</th>
                          <th>Stage</th>
                          <th>Ordered Qty</th>
                          <th>Delivered Qty</th>
                          <th>Returned Qty</th>
                          <th>Net Delivered</th>
                          <th>Remaining Qty</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($remainingItems->count())
                          @php $product_id = 0; $size = 0; @endphp
                          @foreach($remainingItems as $item)
                          <tr>
                            <td>{{$loop->index + 1}}</td>
                            @if($item->product_name == $product_id)
                              <td colspan="2"></td>
                            @else
                              <td>{{$item->article_no}}</td>
                              <td>{{$item->product_name}}</td>
                            @endif
                            @if($item->size_name == $size && $item->product_name == $product_id)
                              <td></td>
                            @else
                              <td>{{$item->size_name}}</td>
                            @endif
                            <td>{{$item->stage_name}}</td>
                            <td>{{number_format($item->ordered_quantity)}} {{$item->unit_name}}</td>
                            <td>
                              <span class="badge badge-success">{{number_format($item->delivered_quantity)}} {{$item->unit_name}}</span>
                            </td>
                            <td>
                              @if($item->returned_quantity > 0)
                                <span class="badge badge-danger">{{number_format($item->returned_quantity)}} {{$item->unit_name}}</span>
                              @else
                                <span class="badge badge-secondary">0 {{$item->unit_name}}</span>
                              @endif
                            </td>
                            <td>
                              <span class="badge badge-info">{{number_format($item->net_delivered_quantity)}} {{$item->unit_name}}</span>
                            </td>
                            <td>
                              @if($item->remaining_quantity > 0)
                                <span class="badge badge-warning">{{number_format($item->remaining_quantity)}} {{$item->unit_name}}</span>
                              @else
                                <span class="badge badge-success">0 {{$item->unit_name}}</span>
                              @endif
                            </td>
                            <td>
                              @if($item->remaining_quantity <= 0)
                                <span class="badge badge-success">Completed</span>
                              @elseif($item->net_delivered_quantity > 0)
                                <span class="badge badge-warning">Partially Delivered</span>
                              @else
                                <span class="badge badge-danger">Pending</span>
                              @endif
                            </td>
                          </tr>
                          @php $product_id = $item->product_name; $size = $item->size_name @endphp
                          @endforeach
                        @else
                          <tr>
                            <td colspan="11" class="text-center">No items found for this order</td>
                          </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Size</th>
                          <th>Stage</th>
                          <th>Ordered Qty</th>
                          <th>Delivered Qty</th>
                          <th>Returned Qty</th>
                          <th>Net Delivered</th>
                          <th>Remaining Qty</th>
                          <th>Status</th>
                        </tr>
                      </tfoot>
                    </table>

                    {{-- Summary Statistics --}}
                    <div class="row mt-4">
                      <div class="col-md-12">
                        <div class="card">
                          <div class="card-header">
                            <h6>Delivery Summary</h6>
                          </div>
                          <div class="card-body">
                            <div class="row">
                              @php
                                $totalItems = $remainingItems->count();
                                $completedItems = $remainingItems->where('remaining_quantity', '<=', 0)->count();
                                $partialItems = $remainingItems->where('net_delivered_quantity', '>', 0)->where('remaining_quantity', '>', 0)->count();
                                $pendingItems = $remainingItems->where('net_delivered_quantity', '<=', 0)->count();
                                $completionPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0;
                              @endphp
                              <div class="col-md-3">
                                <div class="text-center">
                                  <h4 class="text-primary">{{$totalItems}}</h4>
                                  <p class="mb-0">Total Items</p>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="text-center">
                                  <h4 class="text-success">{{$completedItems}}</h4>
                                  <p class="mb-0">Completed</p>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="text-center">
                                  <h4 class="text-warning">{{$partialItems}}</h4>
                                  <p class="mb-0">Partially Delivered</p>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="text-center">
                                  <h4 class="text-danger">{{$pendingItems}}</h4>
                                  <p class="mb-0">Pending</p>
                                </div>
                              </div>
                            </div>
                            <div class="row mt-3">
                              <div class="col-md-12">
                                <div class="progress">
                                  <div class="progress-bar bg-success" role="progressbar" style="width: {{$completionPercentage}}%" aria-valuenow="{{$completionPercentage}}" aria-valuemin="0" aria-valuemax="100">
                                    {{$completionPercentage}}% Complete
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
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
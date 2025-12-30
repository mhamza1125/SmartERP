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
                <a class="btn btn-info" href="{{ route('order.production', $order['order_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Production Order
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
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
                      @if($order['order_status'] == 1) <span class="badge badge-secondary">Draft</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Confirmed</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-info">Dispatched</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-primary">Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-danger">Cancelled</span>
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
                  {{-- Order Details Tab (Grouped by Product/Size with Stage Modal) --}}
                  <div class="tab-pane fade show active" id="order" role="tabpanel" aria-labelledby="order-tab">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Size</th>
                          <th>Ordered Qty</th>
                          <th>Total Stock</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($stock->count())
                          @php
                            // Group stock by product_type_id to show one row per product/size
                            $groupedStock = $stock->groupBy('product_type_id');
                            $rowIndex = 1;
                            $prevProductId = 0;
                          @endphp
                          @foreach($groupedStock as $productTypeId => $stageItems)
                            @php
                              $firstItem = $stageItems->first();
                              // Calculate total stock across all stages
                              $totalStock = $stageItems->sum(function($item) {
                                return $item->stockIn - $item->stockOut;
                              });
                            @endphp
                            <tr>
                              <td>{{ $rowIndex++ }}</td>
                              @if($firstItem->product_id == $prevProductId)
                                <td colspan="2"></td>
                              @else
                                <td>{{ $firstItem->article_no }}</td>
                                <td>{{ $firstItem->name }}</td>
                              @endif
                              <td>{{ $firstItem->sname }}</td>
                              <td><span class="badge badge-info">{{ number_format($firstItem->ordered_qty ?? 0) }} {{ $firstItem->uname }}</span></td>
                              <td>
                                @if($totalStock > 0)
                                  <span class="badge badge-success">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span>
                                @elseif($totalStock < 0)
                                  <span class="badge badge-danger">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span>
                                @else
                                  <span class="badge badge-secondary">0 {{ $firstItem->uname }}</span>
                                @endif
                              </td>
                              <td>
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#orderStageModal{{ $productTypeId }}">
                                  <i class="fas fa-layer-group"></i> View Stages ({{ $stageItems->count() }})
                                </button>
                                <a href="{{ route('product.ptc', $firstItem->product_id) }}" class="btn btn-sm btn-warning" title="Print PTC" target="_blank">
                                  <i class="fas fa-file-alt"></i> Print PTC
                                </a>
                              </td>
                            </tr>
                            @php $prevProductId = $firstItem->product_id; @endphp
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Size</th>
                          <th>Ordered Qty</th>
                          <th>Total Stock</th>
                          <th>Actions</th>
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

<!-- Stage Detail Modals for Order Status -->
@if($stock->count())
  @php $groupedStockForModals = $stock->groupBy('product_type_id'); @endphp
  @foreach($groupedStockForModals as $productTypeId => $stageItems)
    @php
      $firstItem = $stageItems->first();
      $totalStock = $stageItems->sum(function($item) { return $item->stockIn - $item->stockOut; });
    @endphp
    <div class="modal fade" id="orderStageModal{{ $productTypeId }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <i class="fas fa-layer-group"></i> Stage Breakdown: {{ $firstItem->article_no }} - {{ $firstItem->name }} ({{ $firstItem->sname }})
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info mb-3">
              <strong>Product:</strong> {{ $firstItem->name }} |
              <strong>Article:</strong> {{ $firstItem->article_no }} |
              <strong>Size:</strong> {{ $firstItem->sname }} |
              <strong>Ordered:</strong> {{ number_format($firstItem->ordered_qty ?? 0) }} {{ $firstItem->uname }} |
              <strong>Total Stock:</strong> {{ number_format($totalStock) }} {{ $firstItem->uname }}
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Sr.</th>
                    <th>Stage</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Available</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($stageItems as $stageIdx => $stageItem)
                    @php $stageStock = $stageItem->stockIn - $stageItem->stockOut; @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td><span class="badge badge-primary">{{ $stageItem->stname ?? 'N/A' }}</span></td>
                      <td>{{ number_format($stageItem->stockIn) }}</td>
                      <td>{{ number_format($stageItem->stockOut) }}</td>
                      <td>
                        @if($stageStock > 0)
                          <span class="badge badge-success">{{ number_format($stageStock) }} {{ $stageItem->uname }}</span>
                        @elseif($stageStock < 0)
                          <span class="badge badge-danger">{{ number_format($stageStock) }} {{ $stageItem->uname }}</span>
                        @else
                          <span class="badge badge-secondary">0 {{ $stageItem->uname }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="table-info">
                    <th colspan="4" class="text-right">Total:</th>
                    <th>
                      @if($totalStock > 0)
                        <span class="badge badge-success">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span>
                      @elseif($totalStock < 0)
                        <span class="badge badge-danger">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span>
                      @else
                        <span class="badge badge-secondary">0 {{ $firstItem->uname }}</span>
                      @endif
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif
@endsection

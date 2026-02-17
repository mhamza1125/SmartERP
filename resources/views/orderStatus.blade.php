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
                    <tr>
                      <td><b>Customer No:</b> {{$order['customer_no']}}</td>
                    </tr>
                    <tr>
                      <td><b>Customer Name:</b> {{$order['fname']}} {{$order['lname']}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><b>Order No</b> {{$order['order_no']}}</td>
                    </tr>
                    <tr>
                      <td><b>Job No:</b> {{$order['job_no']}}</td>
                    </tr>
                    <tr>
                      <td><b>Date:</b> {{\Carbon\Carbon::parse($order['order_date'])->format('d-m-Y')}}</td>
                    </tr>
                    <tr>
                      <td><b>Order Status:</b>
                        @if($order['order_status'] == 1) <span class="badge badge-secondary">Draft</span>
                        @elseif($order['order_status'] == 2) <span class="badge badge-success">Confirmed</span>
                        @elseif($order['order_status'] == 3) <span class="badge badge-info">Dispatched</span>
                        @elseif($order['order_status'] == 4) <span class="badge badge-primary">Delivered</span>
                        @elseif($order['order_status'] == 5) <span class="badge badge-danger">Cancelled</span>
                        @else @endif
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                @php
                // Pre-group stock data by product_type_id for easy lookup of total stock
                $groupedStock = $stock->groupBy('product_type_id');
                @endphp
                <div class="table-responsive">
                  <table class="table table-sm table-striped">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Article No</th>
                        <th>Item / Product</th>
                        <th>Size</th>
                        <th>Stage</th>
                        <th>Ordered Qty</th>
                        <th>Total Stock</th>
                        <th>Delivered Qty</th>
                        <th>Returned Qty</th>
                        <!-- <th>Net Delivered</th> -->
                        <th>Remaining Qty</th>
                        <!-- <th>Status</th> -->
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($remainingItems->count())
                      @php $prev_product_name = ''; $prev_size_name = ''; $rowIndex = 1; @endphp
                      @foreach($remainingItems as $item)
                      @php
                      // Calculate total stock for this product + size
                      $stageStockItems = $groupedStock->get($item->product_type_id, collect());
                      $totalStock = $stageStockItems->sum(function($si) {
                      return ($si->stockIn ?? 0) - ($si->stockOut ?? 0);
                      });

                      $isNewProduct = ($item->product_name != $prev_product_name);
                      $isNewSize = ($isNewProduct || $item->size_name != $prev_size_name);
                      @endphp
                      <tr>
                        <td>{{ $rowIndex++ }}</td>
                        @if($isNewProduct)
                        <td>{{$item->article_no}}</td>
                        <td>{{$item->product_name}}</td>
                        @else
                        <td colspan="2"></td>
                        @endif

                        @if($isNewSize)
                        <td>{{$item->size_name}}</td>
                        @else
                        <td></td>
                        @endif

                        <td>{{$item->stage_name}}</td>
                        <td>{{number_format($item->ordered_quantity)}} {{$item->unit_name}}</td>

                        {{-- Total Stock Column from deleted tab --}}
                        <td>
                          @if($isNewSize)
                          @if($totalStock > 0)
                          <span class="badge badge-success">{{ number_format($totalStock) }} {{$item->unit_name}}</span>
                          @elseif($totalStock < 0)
                            <span class="badge badge-danger">{{ number_format($totalStock) }} {{$item->unit_name}}</span>
                            @else
                            <span class="badge badge-secondary">0 {{$item->unit_name}}</span>
                            @endif
                            @endif
                        </td>

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
                        <!-- <td>
                          <span class="badge badge-info">{{number_format($item->net_delivered_quantity)}} {{$item->unit_name}}</span>
                        </td> -->
                        <td>
                          @if($item->remaining_quantity > 0)
                          <span class="badge badge-warning">{{number_format($item->remaining_quantity)}} {{$item->unit_name}}</span>
                          @else
                          <span class="badge badge-success">0 {{$item->unit_name}}</span>
                          @endif
                        </td>
                        <!-- <td>
                          @if($item->remaining_quantity <= 0)
                            <span class="badge badge-success">Completed</span>
                            @elseif($item->net_delivered_quantity > 0)
                            <span class="badge badge-warning">Partially Delivered</span>
                            @else
                            <span class="badge badge-danger">Pending</span>
                            @endif
                        </td> -->

                        {{-- Actions Column from deleted tab --}}
                        <td>
                          @if($isNewSize)
                          <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#orderStageModal{{ $item->product_type_id }}" title="View Stages">
                            <i class="fas fa-layer-group"></i>
                          </button>
                          <a href="{{ route('product.ptc', $item->product_id) }}" class="btn btn-sm btn-warning" title="Print PTC" target="_blank">
                            <i class="fas fa-file-alt"></i>
                          </a>
                          @endif
                        </td>
                      </tr>
                      @php
                      $prev_product_name = $item->product_name;
                      $prev_size_name = $item->size_name;
                      @endphp
                      @endforeach
                      @else
                      <tr>
                        <td colspan="13" class="text-center">No items found for this order</td>
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
                        <th>Total Stock</th>
                        <th>Delivered Qty</th>
                        <th>Returned Qty</th>
                        <!-- <th>Net Delivered</th> -->
                        <th>Remaining Qty</th>
                        <!-- <th>Status</th> -->
                        <th>Actions</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>

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
                              <div class="col-md-3 col-6">
                                <div class="text-center">
                                  <h4 class="text-primary">{{$totalItems}}</h4>
                                  <p class="mb-0 text-muted">Total Items</p>
                                </div>
                              </div>
                              <div class="col-md-3 col-6">
                                <div class="text-center">
                                  <h4 class="text-success">{{$completedItems}}</h4>
                                  <p class="mb-0 text-muted">Completed</p>
                                </div>
                              </div>
                              <div class="col-md-3 col-6">
                                <div class="text-center">
                                  <h4 class="text-warning">{{$partialItems}}</h4>
                                  <p class="mb-0 text-muted">Partial</p>
                                </div>
                              </div>
                              <div class="col-md-3 col-6">
                                <div class="text-center">
                                  <h4 class="text-danger">{{$pendingItems}}</h4>
                                  <p class="mb-0 text-muted">Pending</p>
                                </div>
                              </div>
                        </div>
                        <div class="row mt-3">
                          <div class="col-md-12">
                            <div class="progress" style="height: 20px;">
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
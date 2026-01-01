@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Requirements for Order</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
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
                    <tr><td><b>Date:</b> {{\Carbon\Carbon::parse($order['order_date'])->format('d-m-Y')}}</td></tr>
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
              <div class="col-md-12">
                <h5 class="mt-4 mb-3">Materials Required</h5>
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="save-stage" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Current Vendor</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Purchased Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($estimate->count())
                        @php $srNo = 1; @endphp
                        @foreach($estimate as $item)
                            @if(!isset($item->is_component_product) || !$item->is_component_product)
                                @php
                                    $stockItem = $stock[$item->material_id] ?? (object)[
                                      'total_qty' => 0, 'stockIn' => 0, 'total_received' => '0',
                                      'total_returned' => 0, 'stockOut' => 0
                                    ];
                                    $available = $stockItem->total_received + $stockItem->stockIn - $stockItem->total_returned - $stockItem->stockOut;
                                    $purchaseItem = $purchase[$item->material_id] ?? null;
                                    $purchased = $purchaseItem ? $purchaseItem['total_qty'] : 0;
                                @endphp
                                <tr>
                                    <td>{{ $srNo }}</td>
                                    <td>{{ $item->material_no }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->vendor_no }} - {{ $item->fname }}</td>
                                    <td>{{ number_format($item->total_qty, 2) }}</td>
                                    <td>{{ number_format($available) }}</td>
                                    <td>{{ number_format($purchased) }}</td>
                                    <td>{{ number_format(max($item->total_qty - $available, 0)) }}</td>
                                    <td>{{ $item->hname }}</td>
                                </tr>
                                @php $srNo++; @endphp
                            @endif
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Current Vendor</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Purchased Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            @php
              $componentProducts = $estimate->filter(function($item) {
                return isset($item->is_component_product) && $item->is_component_product;
              });
            @endphp

            @if($componentProducts->count() > 0)
            <div class="row">
              <div class="col-md-12">
                <h5 class="mt-4 mb-3">Component Products Required</h5>
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="component-products-table" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Article No</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php $srNo = 1; @endphp
                      @foreach($componentProducts as $item)
                          @php
                              $productStockItem = $productStock[$item->product_type_id] ?? null;
                              $available = 0;
                              if ($productStockItem) {
                                  // Handle both array and object access
                                  $stockIn = is_array($productStockItem) ? $productStockItem['stockIn'] : $productStockItem->stockIn;
                                  $stockOut = is_array($productStockItem) ? $productStockItem['stockOut'] : $productStockItem->stockOut;
                                  $available = $stockIn - $stockOut;
                              }
                          @endphp
                          <tr>
                              <td>{{ $srNo }}</td>
                              <td>{{ $item->article_no }}</td>
                              <td>{{ $item->name }}</td>
                              <td>{{ $item->size_name }}</td>
                              <td>{{ number_format($item->total_qty, 2) }}</td>
                              <td>{{ number_format(max($available, 0)) }}</td>
                              <td>{{ number_format(max($item->total_qty - $available, 0)) }}</td>
                              <td>{{ $item->hname }}</td>
                          </tr>
                          @php $srNo++; @endphp
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Article No</th>
                        <th>Product Name</th>
                        <th>Size</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>
              @if(isset($isMultiOrder) && $isMultiOrder)
                <i class="fas fa-shipping-fast"></i>
                @if(isset($editMode) && $editMode)
                  Edit Multi-Order Delivery
                @else
                  Create Multi-Order Delivery
                @endif
              @else
                Add Delivery
              @endif
            </h4>
            <div class="card-header-action">
              @if(isset($isMultiOrder) && $isMultiOrder)
                <span class="badge badge-light badge-lg mr-2">{{ count($orders) }} Orders Combined</span>
              @endif
              <a href="{{ url()->previous() }}" class="btn {{ isset($isMultiOrder) && $isMultiOrder ? 'btn-light' : 'btn-primary' }}">Back</a>
            </div>
          </div>
          <div class="card-body">
            @if(isset($editMode) && $editMode && isset($existingDelivery))
              <form action="{{ route('delivery.update', $existingDelivery['delivery_id']) }}" method="POST" class="needs-validation" novalidate="">
                @csrf
            @else
              <form action="{{ route('delivery.store') }}" method="POST" class="needs-validation" novalidate="">
                @csrf
            @endif

              @if(isset($isMultiOrder) && $isMultiOrder)
                <!-- Multi-Order Delivery Summary -->
                <div class="card bg-light mb-4">
                  <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-md-8">
                      <h5 class="mb-1"><i class="fas fa-boxes"></i> Multi-Order Delivery Summary <span class="badge badge-secondary ml-2">Multi-Order</span></h5>
                      <p class="mb-0">
                        <strong>Customer:</strong> {{ $customer->fname }} {{ $customer->lname }} ({{ $customer->customer_no }}) |
                        <strong>Total Orders:</strong> {{ count($orders) }} |
                        <strong>Order Numbers:</strong>
                        @foreach($orders as $index => $order)
                          <span class="badge badge-secondary">{{ $order->order_no }}</span>{{ $index < count($orders) - 1 ? ', ' : '' }}
                        @endforeach
                      </p>
                    </div>
                    <div class="col-md-4 text-right">
                      <div class="text-muted small">
                        <strong>Job Numbers:</strong><br>
                        @foreach($orders as $index => $order)
                          {{ $order->job_no }}{{ $index < count($orders) - 1 ? ', ' : '' }}
                        @endforeach
                      </div>
                    </div>
                  </div>
                  </div>
                </div>

                <!-- Hidden field for multi-order delivery -->
                <input type="hidden" name="order_ids" value="{{ $orderIds }}">
                <input type="hidden" name="order_id" value="{{ $orders[0]->order_id }}">
              @else
                <!-- Single Order Information -->
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
              @endif

              <h5 class="mt-2">Delivery Information</h5>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery No <small class="text-muted">(Auto-generated if empty)</small></label>
                    @if(!isset($isMultiOrder) || !$isMultiOrder)
                      <input type="hidden" name="order_id" value="{{$order['order_id']}}" required>
                    @else
                      <!-- Multi-order delivery: use first order as primary and pass all order IDs -->
                      <input type="hidden" name="order_id" value="{{$orders[0]['order_id']}}" required>
                      <input type="hidden" name="order_ids" value="{{$orderIds}}" required>
                      @if(isset($editMode) && $editMode && isset($existingDelivery))
                        <input type="hidden" name="stock_id" value="{{$existingDelivery['stock_id']}}" required>
                        <input type="hidden" name="delivery_id" value="{{$existingDelivery['delivery_id']}}" required>
                      @endif
                    @endif
                    <input type="hidden" name="table_name" value="delivery" required>
                    <input type="hidden" name="employee_id" value="0" required>
                    <input type="hidden" name="stock_type" value="2" required>
                    <input type="hidden" name="stock_status" required value="3">
                    <!-- Delivery No Input Field -->
                    @php
                      $deliveryNoValue = old('delivery_no');
                      if (!$deliveryNoValue && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $deliveryNoValue = $existingDelivery['delivery_no'] ?? '';
                      }
                    @endphp
                    <input type="text" class="form-control" name="delivery_no" placeholder="Leave empty for auto-generation" value="{{$deliveryNoValue}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Delivery No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Stock No <small class="text-muted">(Auto-generated)</small></label>
                    @php
                      // Auto-generate stock_no based on delivery type
                      if (isset($isMultiOrder) && $isMultiOrder && isset($orderIds)) {
                        // Multi-order: pipe-separated order IDs
                        $stockNoValue = $orderIds;
                      } elseif (isset($editMode) && $editMode && isset($existingDelivery)) {
                        // Edit mode: use existing value
                        $stockNoValue = $existingDelivery['stock_no'] ?? '';
                      } else {
                        // Single order: use order ID as stock_no
                        $orderId = isset($order) ? (is_array($order) ? $order['order_id'] ?? '' : $order->order_id ?? '') : '';
                        $stockNoValue = $orderId;
                      }
                    @endphp
                    <input type="text" class="form-control" name="stock_no" placeholder="Stock No" required value="{{old('stock_no', $stockNoValue)}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Stock No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Stock Date</label>
                    @php
                      $deliveryDate = old('stock_date');
                      if (!$deliveryDate && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $deliveryDate = $existingDelivery['stock_date'] ?? '';
                      }
                    @endphp
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{$deliveryDate}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Date</label>
                    @php
                      $actualDeliveryDate = old('delivery_date');
                      if (!$actualDeliveryDate && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $actualDeliveryDate = $existingDelivery['delivery_date'] ?? '';
                      }
                    @endphp
                    <input type="text" class="form-control datepicker" name="delivery_date" value="{{$actualDeliveryDate}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Status</label>
                    @php
                      $orderStatus = old('order_status');
                      if (!$orderStatus && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $orderStatus = $existingDelivery['order_status'] ?? '2';
                      } else if (!$orderStatus) {
                        $orderStatus = '2';
                      }
                    @endphp
                    <select class="form-control select2" name="order_status" required>
                      <option value="" disabled>Select Order Status</option>
                      <option value="1" {{ $orderStatus == '1' ? 'selected' : '' }}>Draft</option>
                      <option value="2" {{ $orderStatus == '2' ? 'selected' : '' }}>Confirmed</option>
                      <option value="3" {{ $orderStatus == '3' ? 'selected' : '' }}>Dispatched</option>
                      <option value="4" {{ $orderStatus == '4' ? 'selected' : '' }}>Delivered</option>
                      <option value="5" {{ $orderStatus == '5' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Order Status</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Status</label>
                    @php
                      $deliveryStatus = old('delivery_status');
                      if (!$deliveryStatus && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $deliveryStatus = $existingDelivery['delivery_status'] ?? '1';
                      } else if (!$deliveryStatus) {
                        $deliveryStatus = '1';
                      }
                    @endphp
                    <select class="form-control select2" name="delivery_status" required>
                      <option value="" disabled>Select Delivery Status</option>
                      <option value="1" {{ $deliveryStatus == '1' ? 'selected' : '' }}>Pending</option>
                      <option value="2" {{ $deliveryStatus == '2' ? 'selected' : '' }}>Dispatched</option>
                      <option value="3" {{ $deliveryStatus == '3' ? 'selected' : '' }}>Delivered</option>
                      <option value="4" {{ $deliveryStatus == '4' ? 'selected' : '' }}>Returned</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Delivery Status</div>
                  </div>
                </div>
              </div>

              <h6 class="mt-2">Shipping From</h6>
              <div class="row">
                <div class="col-md-6">
                  <label>Shipping From</label>
                  @php
                    $fshipping = old('fshipping');
                    if (!$fshipping && isset($editMode) && $editMode && isset($existingDelivery)) {
                      $fshipping = $existingDelivery['fshipping'] ?? '';
                    }
                  @endphp
                  <input type="text" class="form-control" name="fshipping" placeholder="Shipping From" value="{{$fshipping}}">
                </div>
                <div class="col-md-3">
                  <label>Port Name</label>
                  @php
                    $fportNo = old('fport_no');
                    if (!$fportNo && isset($editMode) && $editMode && isset($existingDelivery)) {
                      $fportNo = $existingDelivery['fport_no'] ?? '';
                    }
                  @endphp
                  <input type="text" class="form-control" name="fport_no" placeholder="Port Name" value="{{$fportNo}}">
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Shipping Method</label>
                    @php
                      $deliveryMethod = old('delivery_method');
                      if (!$deliveryMethod && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $deliveryMethod = $existingDelivery['delivery_method'] ?? '';
                      }
                    @endphp
                    <select class="form-control select2" name="delivery_method" required>
                      <option value="" disabled>Select Shipping Method</option>
                      <option value="1" {{ $deliveryMethod == '1' ? 'selected' : '' }}>Sea Freight</option>
                      <option value="2" {{ $deliveryMethod == '2' ? 'selected' : '' }}>Air Freight</option>
                      <option value="3" {{ $deliveryMethod == '3' ? 'selected' : '' }}>Road Transport</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Shipping Method</div>
                  </div>
                </div>
              </div>

              <h6>Shipping To</h6>
              <div class="row">
                <div class="col-md-6">
                  <label>Shipping To</label>
                  @php
                    $tshipping = old('tshipping');
                    if (!$tshipping && isset($editMode) && $editMode && isset($existingDelivery)) {
                      $tshipping = $existingDelivery['tshipping'] ?? '';
                    }
                    // Auto-populate from customer address (server-side)
                    if (!$tshipping && isset($customer)) {
                      $tshipping = $customer['address'] ?? '';
                    }
                  @endphp
                  <input type="text" class="form-control" id="tshipping" name="tshipping" placeholder="Shipping To" value="{{$tshipping}}">
                </div>
                <div class="col-md-6">
                  <label>Port Name</label>
                  @php
                    $tportNo = old('tport_no');
                    if (!$tportNo && isset($editMode) && $editMode && isset($existingDelivery)) {
                      $tportNo = $existingDelivery['tport_no'] ?? '';
                    }
                    // Auto-populate from customer port_no (server-side)
                    if (!$tportNo && isset($customer)) {
                      $tportNo = $customer['port_no'] ?? '';
                    }
                  @endphp
                  <input type="text" class="form-control" id="tport_no" name="tport_no" placeholder="Port Name" value="{{$tportNo}}">
                </div>
              </div>

              <h6 class="mt-4">Commercial Invoice Information</h6>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>FI No <small class="text-muted">(Optional)</small></label>
                    @php
                      $fiNo = old('fi_no');
                      if (!$fiNo && isset($editMode) && $editMode && isset($existingDelivery)) {
                        $fiNo = $existingDelivery['fi_no'] ?? '';
                      }
                    @endphp
                    <input type="text" class="form-control" name="fi_no" placeholder="FI Number" value="{{$fiNo}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>REX No <small class="text-muted">(From Company)</small></label>
                    <input type="text" class="form-control" value="{{ $company->rex_no ?? 'N/A' }}" readonly>
                    <small class="form-text text-muted">This field is managed in Company Settings</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>NTN <small class="text-muted">(From Company)</small></label>
                    <input type="text" class="form-control" value="{{ $company->ntn ?? 'N/A' }}" readonly>
                    <small class="form-text text-muted">This field is managed in Company Settings</small>
                  </div>
                </div>
              </div>

              @if(isset($isMultiOrder) && $isMultiOrder)
                <h5 class="mt-4"><i class="fas fa-list-alt"></i> Selected Orders <span class="badge badge-info">{{ count($orders) }} Orders Combined</span></h5>
              @else
                <h5 class="mt-4">Ordered Items / Products</h5>
              @endif
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-sm table-striped">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Stage</th>
                        <th>Ordered / Delivered</th>
                        <th>Remaining Qty</th>
                        <th>Per Box Qty</th>
                        <th>Available Qty / Boxes</th>
                        <th>Deliver Qty</th>
                        <th>Box Qty</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(isset($isMultiOrder) && $isMultiOrder)
                        @if(isset($orderItems) && count($orderItems) > 0)
                          @php
                            $aggregatedItems = [];
                            foreach($orderItems as $item) {
                              $item = (object) $item;
                              $key = $item->product_type_id . '_' . $item->stage_id;
                              if (!isset($aggregatedItems[$key])) {
                                $aggregatedItems[$key] = $item;
                              } else {
                                $aggregatedItems[$key]->quantity += $item->quantity;
                                // Don't sum stockIn, stockOut or stockOutDelivered - they represent totals for this product/stage
                                // Just keep the value from the first item (they should all be the same)
                                // stockIn, stockOut and stockOutDelivered are NOT summed - they're already totals
                              }
                            }
                            $index = 1;
                          @endphp
                          @foreach($aggregatedItems as $item)
                            @php
                              // Calculate available quantity for delivery
                              $availableQty = $item->stockIn - $item->stockOut;
                              // Calculate remaining to deliver (ordered - already delivered)
                              $remainingQty = $item->quantity - $item->stockOutDelivered;
                              // Actual deliverable is the minimum of available and remaining
                              $deliverableQty = min($availableQty, $remainingQty);

                              // When editing, get the existing delivered quantity for this item
                              $existingDeliveredQty = 0;
                              if (isset($editMode) && $editMode && isset($deliveryItem) && count($deliveryItem) > 0) {
                                foreach ($deliveryItem as $dItem) {
                                  if ($dItem['product_type_id'] == $item->product_type_id && $dItem['stage_id'] == $item->stage_id) {
                                    $existingDeliveredQty = $dItem['quantity'] ?? 0;
                                    break;
                                  }
                                }
                              }
                            @endphp
                            @unless($deliverableQty <= 0)
                              <tr>
                                <td>{{$index++}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}
                                  <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}" required>
                                  <input type="hidden" name="material_id[]" value="0" required>
                                </td>
                                <td>{{$item->stname}}
                                  <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}" required>
                                </td>
                                <td>{{number_format($item->quantity)}} / {{number_format($item->stockOutDelivered)}}</td>
                                <td>{{number_format($remainingQty)}}</td>
                                <td>{{$item->bqty > 0 ? number_format(1/$item->bqty) : '0'}} {{$item->uname}}</td>
                                <td>{{number_format($availableQty)}} {{$item->uname}} / {{number_format($availableQty*$item->bqty, 2)}} boxes</td>
                                <td class="form-group">
                                  <input type="number" class="form-control quantity-input" name="quantity[]" value="{{$existingDeliveredQty}}" min="0" max="{{$deliverableQty}}" data-bqty="{{$item->bqty}}" data-remaining="{{$remainingQty}}" style="width:100px">
                                </td>
                                <td class="form-group">
                                  <input type="number" class="form-control bqty-input" value="{{$existingDeliveredQty * $item->bqty}}" style="width:100px" readonly>
                                </td>
                                <td>
                                  <button class="btn btn-success btn-sm maxBtn">Max</button>
                                  <button class="btn btn-warning btn-sm zeroBtn">Zero</button>
                                </td>
                              </tr>
                            @endunless
                          @endforeach
                        @endif
                      @else
                        @if(count($stock))
                        @php
                          $aggregatedItems = [];
                          foreach($stock as $item) {
                            $item = (object) $item;
                            $key = $item->product_type_id . '_' . $item->stage_id;
                            if (!isset($aggregatedItems[$key])) {
                              $aggregatedItems[$key] = $item;
                            } else {
                              $aggregatedItems[$key]->quantity += $item->quantity;
                              // Don't sum stockOut or stockOutDelivered - they represent totals for this product/stage
                              // Just keep the value from the first item (they should all be the same)
                              $aggregatedItems[$key]->stockIn += $item->stockIn;
                              // stockOut and stockOutDelivered are NOT summed - they're already totals
                            }
                          }
                          $index = 1;
                          $hasDeliverableItems = false; 
                        @endphp
                          @foreach($aggregatedItems as $item)
                            @php
                              // Calculate available quantity for delivery
                              $availableQty = $item->stockIn - $item->stockOut;
                              // Calculate remaining to deliver (ordered - already delivered)
                              $remainingQty = $item->quantity - $item->stockOutDelivered;
                              // Actual deliverable is the minimum of available and remaining
                              $deliverableQty = min($availableQty, $remainingQty);

                              // When editing, get the existing delivered quantity for this item
                              $existingDeliveredQty = 0;
                              if (isset($editMode) && $editMode && isset($deliveryItem) && count($deliveryItem) > 0) {
                                foreach ($deliveryItem as $dItem) {
                                  if ($dItem['product_type_id'] == $item->product_type_id && $dItem['stage_id'] == $item->stage_id) {
                                    $existingDeliveredQty = $dItem['quantity'] ?? 0;
                                    break;
                                  }
                                }
                              }
                            @endphp
                            @unless($deliverableQty <= 0)
                              @php $hasDeliverableItems = true; @endphp 
                              <tr>
                                <td>{{$index++}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}
                                  <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}" required>
                                  <input type="hidden" name="material_id[]" value="0" required>
                                </td>
                                <td>{{$item->stname}}
                                  <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}" required>
                                </td>
                                <td>{{number_format($item->quantity)}} / {{number_format($item->stockOutDelivered)}}</td>
                                <td>{{number_format($remainingQty)}}</td>
                                <td>{{$item->bqty > 0 ? number_format(1/$item->bqty) : '0'}} {{$item->uname}}</td>
                                <td>{{number_format($availableQty)}} {{$item->uname}} / {{number_format($availableQty*$item->bqty, 2)}} boxes</td>
                                <td class="form-group">
                                  <input type="number" class="form-control quantity-input" name="quantity[]" value="{{$existingDeliveredQty}}" min="0" max="{{$deliverableQty}}" data-bqty="{{$item->bqty}}" data-remaining="{{$remainingQty}}" style="width:100px">
                                </td>
                                <td class="form-group">
                                  <input type="number" class="form-control bqty-input" value="{{$existingDeliveredQty * $item->bqty}}" style="width:100px" readonly>
                                </td>
                                <td>
                                  <button class="btn btn-success btn-sm maxBtn">Max</button>
                                  <button class="btn btn-warning btn-sm zeroBtn">Zero</button>
                                </td>
                              </tr>
                            @endunless
                          @endforeach

                          {{-- ✅ SINGLE MESSAGE ONLY --}}
                          @if(!$hasDeliverableItems)
                            <tr class="text-muted">
                              <td colspan="10" class="text-center">
                                <em>No deliverable quantity available.</em>
                              </td>
                            </tr>
                          @endif
                        @endif
                      @endif
                        <tr>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <th> Total Qty: <span id="totalQuantity">0</span> </th>
                          <th> Total Boxes: <span id="totalBqty">0</span> </th>
                          <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Stage</th>
                        <th>Ordered / Delivered</th>
                        <th>Remaining Qty</th>
                        <th>Per Box Qty</th>
                        <th>Available Qty / Boxes</th>
                        <th>Deliver Qty</th>
                        <th>Box Qty</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <h5 class="mt-4">Delivery Container / Vehicle</h5>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Container / Vehicle</label>
                    <select class="form-control select2" name="smaterial_id[]" id="materialSelect">
                      <option value="" disabled selected>Select Material</option>
                      @if(count($vehicle))
                        @foreach($vehicle as $item)
                          @php $item = (object) $item; $available = $item->total_received - $item->total_returned + $item->stockIn - $item->stockOut @endphp
                          <option value="{{$item->material_id}}" data-available="{{$available}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>{{$item->material_no}} - {{$item->name}} - Qty [{{$available}}]</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="squantity" placeholder="0" id="vehicleQty">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtn" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Container / Vehicle</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                  </table>
                </div>
              </div>

              <h5 class="mt-4">Factory to Container Delivery</h5>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vehicle No</label>
                    <input type="text" class="form-control" name="svehicle_no" placeholder="Vehicle No">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 1</label>
                    <input type="text" class="form-control" name="sQty1[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 2</label>
                    <input type="text" class="form-control" name="sQty2[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 3</label>
                    <input type="text" class="form-control" name="sQty3[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 4</label>
                    <input type="text" class="form-control" name="sQty4[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 5</label>
                    <input type="text" class="form-control" name="sQty5[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 6</label>
                    <input type="text" class="form-control" name="sQty6[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 7</label>
                    <input type="text" class="form-control" name="sQty7[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Row 8</label>
                    <input type="text" class="form-control" name="sQty8[]" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addVBtn" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="vehicles-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Vehicle No</th>
                        <th>Row 1</th>
                        <th>Row 2</th>
                        <th>Row 3</th>
                        <th>Row 4</th>
                        <th>Row 5</th>
                        <th>Row 6</th>
                        <th>Row 7</th>
                        <th>Row 8</th>
                        <th>Total Qty</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th colspan="6"></th>
                        <th colspan="2">Grand Total:</th>
                        <th colspan="2"><span id="tQty"></span> / <span id="totalBqty2">0</span> Boxes</th>
                        <th colspan="2"> Remaining: <span id="remBqty">0</span> Boxes</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <h5 class="mt-2">Delivery Expense</h5>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Expense</label>
                    <select class="form-control select2" name="spayee_id">
                      <option value="" selected disabled>Select Expense Head</option>
                      @if(count($expense))
                        @foreach($expense as $item)
                          @php $item = (object) $item; @endphp
                          <option value="{{$item->head_id}}" {{ old('payee_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Cash / Bank (if any)</label>
                    <select class="form-control select2" name="sbank_id">
                      <option value="0" selected>Cash Payment</option>
                      @if(count($bank))
                        @foreach($bank as $item)
                          @php $item = (object) $item; @endphp
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->head_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" min="0" class="form-control" name="sdebit">
                  </div>
                </div>
                <div class="col-md-3">
                  <label>Detail</label>
                  <textarea class="form-control" name="sremarks[]"></textarea>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addExpenseBtn" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="expense-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Expense</th>
                        <th>Bank / Cash</th>
                        <th>Amount</th>
                        <th>Detail</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{old('description')}}</textarea>
                  </div>
                </div>
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
var isDeliveryPage = true;

// Multi-Order Selection Enhancement
$(document).ready(function() {
    // Add "Select All" functionality
    if ($('input[name="selected_orders[]"]').length > 1) {
        var selectAllHtml = `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="select_all_orders">
                <label class="form-check-label" for="select_all_orders">
                    <strong>Select All Orders</strong>
                </label>
            </div>
        `;
        $('input[name="selected_orders[]"]').first().closest('table').before(selectAllHtml);

        // Select All functionality
        $('#select_all_orders').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('input[name="selected_orders[]"]:not(:disabled)').prop('checked', isChecked);
        });

        // Update Select All when individual checkboxes change
        $('input[name="selected_orders[]"]').on('change', function() {
            var totalCheckboxes = $('input[name="selected_orders[]"]:not(:disabled)').length;
            var checkedCheckboxes = $('input[name="selected_orders[]"]:not(:disabled):checked').length;
            $('#select_all_orders').prop('checked', totalCheckboxes === checkedCheckboxes);
        });
    }

    // Show selected orders count
    $('input[name="selected_orders[]"]').on('change', function() {
        var selectedCount = $('input[name="selected_orders[]"]:checked').length;
        var totalCount = $('input[name="selected_orders[]"]').length;

        // Update or create status message
        var statusMsg = `Selected ${selectedCount} of ${totalCount} orders for this delivery.`;
        if ($('#order-selection-status').length) {
            $('#order-selection-status').text(statusMsg);
        } else {
            $('input[name="selected_orders[]"]').first().closest('.form-group').append(
                `<small id="order-selection-status" class="form-text text-muted">${statusMsg}</small>`
            );
        }
    });

    // Trigger initial count
    if ($('input[name="selected_orders[]"]').length > 0) {
        $('input[name="selected_orders[]"]').first().trigger('change');
    }
});
</script>
@endsection

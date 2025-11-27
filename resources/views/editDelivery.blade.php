@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Status @if(isset($isMultiOrder) && $isMultiOrder) <span class="badge badge-info ml-2">Multi-Order</span> @endif</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('purchase.add')}}" class="btn btn-primary" target="_blank">Purchase</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('delivery.update', $order['delivery_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
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
                      <tr><td><b>Order No</b> {{is_array($order) ? $order['order_no'] : $order->order_no}}</td></tr>
                      <tr><td><b>Job No:</b> {{is_array($order) ? $order['job_no'] : $order->job_no}}</td></tr>
                      @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
                      <tr><td><b>Related Orders:</b>
                        @foreach($relatedOrders as $index => $relOrder)
                          @if($index < 3)
                          <span class="badge badge-secondary mr-1">{{$relOrder->order_no ?? 'N/A'}}</span>
                          @endif
                        @endforeach
                        @if(count($relatedOrders) > 3)
                        <span class="badge badge-light">+{{count($relatedOrders) - 3}} more</span>
                        @endif
                      </td></tr>
                      @endif
                      <tr><td><b>Date:</b> {{is_array($order) ? $order['order_date'] : $order->order_date}}</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <h5 class="mt-2">Delivery Information</h5>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery No</label>
                    <input type="hidden" name="order_id" value="{{$order['order_id']}}" required>
                    <input type="hidden" name="table_name" value="delivery" required>
                    <input type="hidden" name="employee_id" value="0" required>
                    <input type="hidden" name="stock_type" value="2" required>
                    <input type="hidden" name="stock_status" required value="3">
                    <input type="hidden" name="stock_id" required value="{{$order['stock_id']}}">
                    <input type="text" class="form-control" name="stock_no" placeholder="Delivery No" required value="{{$order['stock_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Delivery No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{$order['stock_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Status</label>
                    <select class="form-control select2" name="order_status" required>
                      <option value="" selected disabled>Select Order Status</option>
                      <option value="1" {{$order['order_status'] == '1' ? 'selected' : ''}}>Pending</option>
                      <option value="2" {{$order['order_status'] == '2' ? 'selected' : ''}}>Processing</option>
                      <option value="3" {{$order['order_status'] == '3' ? 'selected' : ''}}>On Hold</option>
                      <option value="4" {{$order['order_status'] == '4' ? 'selected' : ''}}>Partially Delivered</option>
                      <option value="5" {{$order['order_status'] == '5' ? 'selected' : ''}}>Delivered</option>
                      <option value="6" {{$order['order_status'] == '6' ? 'selected' : ''}}>Completed</option>
                      <option value="7" {{$order['order_status'] == '7' ? 'selected' : ''}}>Cancelled</option>
                      <option value="8" {{$order['order_status'] == '8' ? 'selected' : ''}}>Returned</option>
                      <option value="9" {{$order['order_status'] == '9' ? 'selected' : ''}}>Disputed</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Order Status</div>
                  </div>
                </div>                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Status</label>
                    <select class="form-control select2" name="delivery_status" required>
                      <option value="" selected disabled>Select Delivery Status</option>
                      <option value="1" {{$order['delivery_status'] == '1' ? 'selected' : ''}}>Pending</option>
                      <option value="2" {{$order['delivery_status'] == '2' ? 'selected' : ''}}>Delivered</option>
                      <option value="3" {{$order['delivery_status'] == '3' ? 'selected' : ''}}>Returned</option>
                      <option value="4" {{$order['delivery_status'] == '4' ? 'selected' : ''}}>Disputed</option>
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
                  <input type="text" class="form-control" name="fshipping" placeholder="Shipping From" value="{{$order['fshipping']}}">
                </div>
                <div class="col-md-3">
                  <label>Port No</label>
                  <input type="text" class="form-control" name="fport_no" placeholder="Port No" value="{{$order['fport_no']}}">
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Shipping Method</label>
                    <select class="form-control select2" name="delivery_method" required>
                      <option value="" selected disabled>Select Shipping Method</option>
                      <option value="1" {{$order['delivery_method'] == '1' ? 'selected' : ''}}>Sea Freight</option>
                      <option value="2" {{$order['delivery_method'] == '2' ? 'selected' : ''}}>Air Freight</option>
                      <option value="3" {{$order['delivery_method'] == '3' ? 'selected' : ''}}>Road Transport</option>
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
                  <input type="text" class="form-control" name="tshipping" placeholder="Shipping To" value="{{$order['tshipping']}}">
                </div>
                <div class="col-md-6">
                  <label>Port No</label>
                  <input type="text" class="form-control" name="tport_no" placeholder="Port No" value="{{$order['tport_no']}}">
                </div>
              </div>

              <h6 class="mt-4">Commercial Invoice Information</h6>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>FI No <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" name="fi_no" placeholder="FI Number" value="{{$order['fi_no'] ?? ''}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>REX No <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" name="rex_no" placeholder="REX Number" value="{{$order['rex_no'] ?? ''}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>NTN <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" name="ntn" placeholder="NTN Number" value="{{$order['ntn'] ?? ''}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Status</label>
                    <select class="form-control select2" name="delivery_status" required>
                      <option value="" selected disabled>Select Status</option>
                      <option value="1" {{$order['delivery_status'] == '1' ? 'selected' : ''}}>Pending</option>
                      <option value="2" {{$order['delivery_status'] == '2' ? 'selected' : ''}}>In Transit</option>
                      <option value="3" {{$order['delivery_status'] == '3' ? 'selected' : ''}}>Delivered</option>
                      <option value="4" {{$order['delivery_status'] == '4' ? 'selected' : ''}}>Cancelled</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Delivery Status</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Statement of Origin <small class="text-muted">(Optional)</small></label>
                    <textarea class="form-control" name="so_origin" rows="3" placeholder="Statement of Origin for commercial invoice">{{$order['so_origin'] ?? ''}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <h5 class="mt-4">Ordered Items / Products</h5>
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
                      @php $deliveryMap = [];
                        foreach ($deliveryItem as $di) {
                            $key = $di['product_type_id'] . '-' . $di['stage_id'];
                            $deliveryMap[$key] = $di['quantity'];
                        } @endphp
                      @if($stock->count())
                      @php $index = 1; @endphp
                        @foreach($stock as $key => $item)
                          @unless(($item->stockIn - $item->stockOut) <= 0)
                            @php  $key = $item->product_type_id . '-' . $item->stage_id;
                              $qty = $deliveryMap[$key] ?? 0; @endphp
                            <tr>
                              <td>{{$index++}}</td>
                              <td>{{$item->article_no}} - Size {{$item->sname}}
                                <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}" required>
                                <input type="hidden" name="material_id[]" value="0" required>
                              </td>
                              <td>{{$item->stname}}
                                <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}" required>
                              </td>
                              <td>{{number_format($item->quantity)}} / {{number_format($item->stockOut - $qty)}}</td>
                              <td>{{number_format($item->quantity - $item->stockOut + $qty)}}</td>
                              <td>{{$item->bqty > 0 ? number_format(1/$item->bqty) : '0'}} {{$item->uname}}</td>
                              <td>{{number_format($item->stockIn - $item->stockOut + $qty)}} {{$item->uname}} / {{number_format(($item->stockIn - $item->stockOut + $qty)*$item->bqty, 2)}} boxes</td>
                              <td class="form-group">
                                <input type="number" class="form-control quantity-input" name="quantity[]" value="{{$qty}}" min="0" max="{{$item->stockIn - $item->stockOut + $qty}}" data-bqty="{{$item->bqty}}" style="width:100px">
                              </td>
                              <td class="form-group">
                                <input type="number" class="form-control bqty-input" value="{{number_format($qty * $item->bqty, 2)}}" style="width:100px" readonly>
                              </td>
                              <td>
                                <button class="btn btn-success btn-sm maxBtn">Max</button>
                                <button class="btn btn-warning btn-sm zeroBtn">Zero</button>
                              </td>
                            </tr>
                          @endunless
                        @endforeach
                      @endif
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
                      @php $deliveryMap = [];
                      foreach ($deliveryItem as $di) {
                          $deliveryMap[$di['material_id']] = $di['quantity'];
                      } @endphp
                      @if($vehicle->count())
                        @foreach($vehicle as $key => $item)
                          @php  $key = $item->material_id;
                            $qty = $deliveryMap[$key] ?? 0;
                            $available = $item->total_received - $item->total_returned + $item->stockIn - $item->stockOut + $qty @endphp
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
                      @if($deliveryItem->count())
                        @php $index = 1; @endphp
                        @foreach($deliveryItem as $item)
                          @unless($item->product_type_id != 0)
                            <tr>
                              <td>{{$index++}}</td>
                              <td>{{$item->material_no}}
                                <input type="hidden" name="material_name[]" value="{{$item->mname}}">
                                <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
                                <input type="hidden" name="product_type_id[]" value="0">
                                <input type="hidden" name="stage_id[]" value="0">
                              </td>
                              <td>{{$item->quantity}}
                                <input type="hidden" name="quantity[]" value="{{$item->quantity}}">
                              </td>
                              <td><button class="deleteRowBtn btn btn-danger">X</button></td>
                            </tr>
                          @endunless
                        @endforeach
                      @endif
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
                      @if($deliveryBox->count())
                        @php $total = 0; @endphp
                        @foreach($deliveryBox as $item)
                          @php
                            $rowQtys = explode('|', $item->rowQty);
                            $total += $item->totalQty
                          @endphp
                          <tr>
                            <td>{{$loop->index + 1}}</td>
                            <td>{{$item->vehicle_no}}
                              <input type="hidden" name="vehicle_no[]" value="{{$item->vehicle_no}}">
                              <input type="hidden" name="rowQty[]" value="{{$item->rowQty}}">
                            </td>
                            @foreach($rowQtys as $qty)
                              <td>{{$qty}}</td>
                            @endforeach
                            <td>{{$item->totalQty}}
                              <input type="hidden" name="totalQty[]" value="{{$item->totalQty}}">
                            </td>
                            <td><button class="deleteRowBtn btn btn-danger">X</button></td>
                          </tr>
                        @endforeach
                      @endif
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
                      @if($expense->count())
                        @foreach($expense as $item)
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
                      @if($bank->count())
                        @foreach($bank as $item)
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
                      @if($transaction->count())
                        @foreach($transaction as $item)
                          <tr>
                            <td>{{$loop->index + 1}}</td>
                            <td>{{$item->name}}
                              <input type="hidden" name="transaction_id[]" value="{{$item->transaction_id}}">
                              <input type="hidden" name="payee_id[]" value="{{$item->payee_id}}">
                            </td>
                            <td>@if(isset($item->bname))
                              {{$item->bname}} - {{$item->account_title}} - {{$item->account}}
                              @else Cash Payment {{$item->bank_id}} @endif
                              <input type="hidden" name="bank_id[]" value="{{$item->bank_id ?? 0}}">
                            </td>
                            <td>{{number_format($item->debit)}}
                              <input type="hidden" name="debit[]" value="{{$item->debit}}">
                            </td>
                            <td>{{$item->description}}
                              <input type="hidden" name="remarks[]" value="{{$item->description}}">
                            </td>
                            <td>
                              <button class="delete-expense-row btn btn-danger">X</button>
                            </td>
                          </tr>
                        @endforeach
                      @endif
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$order['description']}}</textarea>
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
<script> var isDeliveryPage = false; // Disabled bqty validation for deliveries </script>
@endsection
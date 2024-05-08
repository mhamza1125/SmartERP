@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Delivery</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                {{-- <a href="{{ route('purchase.add')}}" class="btn btn-primary" target="_blank">Purchase</a> --}}
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('delivery.store') }}" method="POST" class="needs-validation" novalidate="">
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
                      <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                      <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                      <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
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
                    <input type="text" class="form-control" name="stock_no" placeholder="Delivery No" required value="{{old('stock_no')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Delivery No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Delivery Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{old('stock_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Status</label>
                    <select class="form-control select2" name="order_status" required>
                      <option value="" selected disabled>Select Order Status</option>
                      <option value="1">Pending</option>
                      <option value="2">Processing</option>
                      <option value="3">On Hold</option>
                      <option value="4">Partially Delivered</option>
                      <option value="5">Delivered</option>
                      <option value="6">Completed</option>
                      <option value="7">Cancelled</option>
                      <option value="8">Returned</option>
                      <option value="9">Disputed</option>
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
                      <option value="1">Pending</option>
                      <option value="2">Delivered</option>
                      <option value="3">Returned</option>
                      <option value="4">Disputed</option>
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
                  <input type="text" class="form-control" name="fshipping" placeholder="Shipping From" value="{{old('fshipping')}}">
                </div>
                <div class="col-md-3">
                  <label>Port No</label>
                  <input type="text" class="form-control" name="fport_no" placeholder="Port No" value="{{old('fport_no')}}">
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Shipping Method</label>
                    <select class="form-control select2" name="delivery_method" required>
                      <option value="" selected disabled>Select Shipping Method</option>
                      <option value="1">Sea Freight</option>
                      <option value="2">Air Freight</option>
                      <option value="3">Road Transport</option>
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
                  <input type="text" class="form-control" name="tshipping" placeholder="Shipping To" value="{{old('tshipping')}}">
                </div>
                <div class="col-md-6">
                  <label>Port No</label>
                  <input type="text" class="form-control" name="tport_no" placeholder="Port No" value="{{old('tport_no')}}">
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
                        <th>Available Qty</th>
                        <th>Deliver Qty</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($stock->count())
                      @php $index = 1; @endphp
                        @foreach($stock as $item)
                          @unless(($item->stockIn - $item->stockOut) <= 0)
                            <tr>
                              <td>{{$index++}}</td>
                              <td>{{$item->article_no}} - Size {{$item->sname}}
                                <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}" required>
                                <input type="hidden" name="material_id[]" value="0" required>
                              </td>
                              <td>{{$item->stname}}
                                <td>{{number_format($item->quantity)}} / {{number_format($item->stockOut)}}
                                  <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}" required>
                                </td>
                              </td>
                              <td>{{number_format($item->quantity - $item->stockOut)}}</td>
                              <td>{{number_format($item->stockIn - $item->stockOut)}} {{$item->uname}}</td>
                              <td class="form-group">
                                <input type="number" class="form-control quantity-input" name="quantity[]" value="0" min="0" max="{{$item->stockIn - $item->stockOut}}">
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
                        <th>Ordered / Delivered Qty</th>
                        <th>Remaining Qty</th>
                        <th>Available Qty</th>
                        <th>Deliver Qty</th>
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
                      @if($vehicle->count())
                        @foreach($vehicle as $item)
                          @php $available = $item->total_received - $item->total_returned + $item->stockIn - $item->stockOut @endphp
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
                    <input type="text" min="0" class="form-control" name="sdebit">
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
  $(document).ready(function() {
      // Set max quantity based on selected material
      $('#materialSelect').change(function() {
          var available = $(this).find('option:selected').data('available');
          $('#vehicleQty').attr('max', available);
      });

      // Automatically set quantity to max if typed value is greater
      $('#vehicleQty').on('input', function() {
          var max = parseInt($(this).attr('max'), 10);
          var currentVal = parseInt($(this).val(), 10);
          if (currentVal > max) {
              $(this).val(max);
          }
      });
  });
</script>
<script> var isDeliveryPage = false; </script>
@endsection
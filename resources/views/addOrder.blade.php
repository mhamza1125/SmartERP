@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Order</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('order.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order No</label>
                    <input type="text" class="form-control" name="order_no" placeholder="Order No" required value="{{old('order_no')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Order No</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Job No</label>
                    <input type="text" class="form-control" placeholder="Auto-generated" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Job No</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Customer</label>
                    <select class="form-control select2" id="customer_id" name="customer_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" data-currency="{{$item->currency_name ?? 'PKR'}}" {{ old('customer_id') == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Currency</label>
                    <input type="text" class="form-control" id="customer_currency" placeholder="Currency" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order Status</label>
                    <select class="form-control select2" name="order_status" required>
                      <option value="" selected disabled>Select Order Status</option>
                      <option value="1" selected>Draft</option>
                      <option value="2">Confirmed</option>
                      <option value="3">Dispatched</option>
                      <option value="4">Delivered</option>
                      <option value="5">Cancelled</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order Date</label>
                    <input type="text" class="form-control datepicker" name="order_date" required value="{{old('order_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Due Date <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control datepicker" name="due_date" value="{{old('due_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Payment Terms <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" name="payment_terms" placeholder="Payment Terms" value="{{old('payment_terms')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              {{-- <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Expected Delivery Date <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control datepicker" name="expected_delivery_date" placeholder="Select expected delivery date" value="{{old('expected_delivery_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div> --}}

              <h6>Order Items</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id" id="product_type_id">
                      <option value="" disabled selected>Select Product</option>
                      @if($product->count())
                        @foreach($product as $item)
                          <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->article_no}} - Size {{$item->hname}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="stage_id" id="stage_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled>Select Product Stage</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantity" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtn" class="btn btn-primary">Add</button>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Price (Customer Currency)</label>
                    <input type="number" min="0" class="form-control" name="price" id="price" placeholder="0">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Product Stage</th>
                        <th>Quantity</th>
                        <th>Price (Customer Currency)</th>
                        <th>Total (Customer Currency)</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th></th>
                        <th colspan="3">Grand Total (Customer Currency):</th>
                        <th id="grandTotal" colspan="2">00.00</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description">{{old('description')}}</textarea>
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
  var isOrderPage = false;
  var ajaxPSUrl = "{{ route('ajaxPS') }}";

  // Handle customer currency display
  $(document).ready(function() {
    $('#customer_id').on('change', function() {
      var selectedOption = $(this).find('option:selected');
      var currency = selectedOption.data('currency') || 'PKR';
      $('#customer_currency').val(currency);
    });

    // Set currency on page load if customer is pre-selected
    var selectedOption = $('#customer_id').find('option:selected');
    if (selectedOption.val()) {
      var currency = selectedOption.data('currency') || 'PKR';
      $('#customer_currency').val(currency);
    }
  });
</script>
@endsection
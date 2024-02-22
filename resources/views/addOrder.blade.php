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
              <a href="{{ route('order') }}" class="btn btn-primary">
                View All
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('order.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order No</label>
                    <input type="text" class="form-control" name="order_no" required value="{{old('order_no')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Order No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Job No</label>
                    <input type="text" class="form-control" name="job_no" required value="{{old('job_no')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Job No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Customer</label>
                    <select class="form-control select2" name="customer_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ old('customer_id') == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order Date</label>
                    <input type="text" class="form-control datepicker" name="order_date" required value="{{old('order_date')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Order Date</div>
                  </div>
                </div>
              </div>

              <h6>Order Items</h6>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id[]">
                      <option value="" disabled selected>Select Product</option>
                      @if($product->count())
                        @foreach($product as $item)
                          <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->name}} - Size {{$item->hname}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantity" required value="0">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" min="0" class="form-control" name="price" required value="0">
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
                        <th>Item / Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th></th>
                        <th colspan="3">Grand Total:</th>
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
                    <textarea class="summernote" name="description">{{old('description')}}</textarea>
                  </div>
                </div>
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script> var isOrderPage = false; </script>
@endsection
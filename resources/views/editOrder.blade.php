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
            <form action="{{ route('order.update', $order['order_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order No</label>
                    <input type="text" class="form-control" name="order_no" required value="{{$order['order_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Order No</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Job No</label>
                    <input type="text" class="form-control" name="job_no" required value="{{$order['job_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Job No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Customer</label>
                    <select class="form-control select2" name="customer_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ $order['customer_id'] == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Status</label>
                    <select class="form-control select2" name="order_status" required>
                      <option value="" selected disabled>Select Order Status</option>
                      <option value="1" {{ $order['order_status'] == 1 ? 'selected' : '' }}>Pending</option>
                      <option value="2" {{ $order['order_status'] == 2 ? 'selected' : '' }}>Processing</option>
                      <option value="3" {{ $order['order_status'] == 3 ? 'selected' : '' }}>On Hold</option>
                      <option value="4" {{ $order['order_status'] == 4 ? 'selected' : '' }}>Partially Delivered</option>
                      <option value="5" {{ $order['order_status'] == 5 ? 'selected' : '' }}>Delivered</option>
                      <option value="6" {{ $order['order_status'] == 6 ? 'selected' : '' }}>Completed</option>
                      <option value="7" {{ $order['order_status'] == 7 ? 'selected' : '' }}>Cancelled</option>
                      <option value="8" {{ $order['order_status'] == 8 ? 'selected' : '' }}>Returned</option>
                      <option value="9" {{ $order['order_status'] == 9 ? 'selected' : '' }}>Disputed</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order Date</label>
                    <input type="text" class="form-control datepicker" name="order_date" required value="{{$order['order_date']}}">
                    <div class="valid-feedback">Good job!</div>
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
                          <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->article_no}} - Size {{$item->hname}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantity" placeholder="0">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" min="0" class="form-control" name="price" placeholder="0">
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
                      <tr>
                        @if($orderItem->count())
                          @foreach($orderItem as $item)
                            <tr data-item-id="{{ $item->order_item_id }}">
                              <td></td>
                              <td>{{$item->name}} - Size {{$item->hname}}
                                <input type="hidden" name="name[]" value="{{$item->name}}">
                                <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}">
                              </td>
                              <td>{{$item->quantity}}
                                <input type="hidden" name="quantity[]" value="{{$item->quantity}}"></td>
                              </td>
                              <td>{{$item->price}}
                                <input type="hidden" name="price[]" value="{{$item->price}}"></td>
                              <td>{{$item->quantity * $item->price}}
                                <input type="hidden" name="total[]" value="{{$item->total}}">
                              </td>
                              <td><button class="deleteRowBtn btn btn-danger">X</button></td>
                            </tr>
                          @endforeach
                        @endif
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
                    <textarea class="summernote" name="description">{{$order['description']}}</textarea>
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
<script> var isOrderPage = true; </script>
@endsection
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Purchase</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('purchase.update', $purchase['purchase_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase No</label>
                    <input type="text" class="form-control" name="purchase_no" required value="{{$purchase['purchase_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Purchase No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vendor</label>
                    <select class="form-control select2" name="vendor_id" required>
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ $purchase['vendor_id'] == $item->vendor_id ? 'selected' : '' }}>{{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <select class="form-control select2" name="order_id" required>
                      <option value="0" selected disabled>Default Purchase</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ $purchase['order_id'] == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="text" class="form-control datepicker" name="purchase_date" required value="{{$purchase['purchase_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Required Date</label>
                    <input type="text" class="form-control datepicker" name="require_date" required value="{{$purchase['require_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <h6>Purchase Items</h6>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id[]">
                      <option value="" disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                        <th>Item / Material</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($purchaseItem->count())
                        @foreach($purchaseItem as $item)
                          <tr data-item-id="{{ $item->purchase_item_id }}">
                            <td></td>
                            <td>{{$item->name}}
                              <input type="hidden" name="material_name[]" value="{{$item->name}}">
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
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
                    <textarea class="summernote" name="description">{{$purchase['description']}}</textarea>
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
<script> var isEditPage = true; </script>
@endsection
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Material Processing</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('mprocess.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Material Process No</label>
                    <input type="text" class="form-control" name="purchase_no" required value="{{$count}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Process No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vendor</label>
                    <select class="form-control select2" name="vendor_id" required>
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ old('vendor_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Processing For Order</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      <option value="0" selected>Default Processing</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ old('order_id') == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Processing Date</label>
                    <input type="text" class="form-control datepicker" name="purchase_date" required value="{{old('purchase_date')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Processing Date</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Required Date</label>
                    <input type="text" class="form-control datepicker" name="require_date" required value="{{old('require_date')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Require Date</div>
                  </div>
                </div>
              </div>

              <h6>Processing Items</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Materials A</label>
                    <select class="form-control select2" name="samaterial_id[]" id="amaterial_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}">{{$item->material_no}} - {{$item->name}} | {{$item->uname}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="available_stock">Available Stock</label>
                    <input type="text" class="form-control" id="available_stock" name="available_stock" readonly>
                  </div>
                </div>  
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Quantity A</label>
                    <input type="number" min="0" class="form-control" name="saquantity" placeholder="0">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Materials B</label>
                    <select class="form-control select2" name="sbmaterial_id[]" id="bmaterial_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}">{{$item->material_no}} - {{$item->name}} | {{$item->uname}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>                
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity B</label>
                    <input type="number" min="0" class="form-control" name="sbquantity" placeholder="0">
                  </div>
                </div>
                <div class="col-md-2">
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
                        <th>Materials A</th>
                        <th>Materials B</th>
                        <th>Quantity A</th>
                        <th>Quantity B</th>
                        <th>Price</th>
                        <th>Total <sub>Qty B x Price</sub></th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th colspan="3"></th>
                        <th colspan="2">Grand Total:</th>
                        <th id="grandTotal" colspan="2">00.00</th>
                        <th></th>
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
  var isMProcessPage = false; 
  var stockData = @json($stock);
</script>
@endsection
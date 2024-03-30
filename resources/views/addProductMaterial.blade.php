@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Product Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('productMaterial.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select2" name="product_type_id" required>
                      <option value="" selected disabled>Select Product</option>
                      @if($product->count())
                        @foreach($product as $item)
                          <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->article_no}} - Size {{$item->hname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Product</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
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
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
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

              <h6>Product Packing</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Boxes</label>
                    <select class="form-control select2" name="box_id" required>
                      <option value="" disabled selected>Select Boxes</option>
                      @if($box->count())
                        @foreach($box as $item)
                          <option value="{{$item->box_id}}" {{ old('box_id') == $item->box_id ? 'selected' : '' }}>{{$item->box_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Unit in Box</label>
                    <input type="number" min="0" class="form-control" name="bqty" placeholder="0" value="{{old('bqty')}}" required>
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
<script> var isPMPage = false; </script>
@endsection
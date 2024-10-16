@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('material.update', $material['material_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Material No</label>
                    <input type="text" class="form-control" name="material_no" required value="{{$material['material_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Material No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Material Name</label>
                    <input type="text" class="form-control" name="name" required value="{{$material['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Material Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Unit</label>
                    <select class="form-control select2" name="unit_id" required>
                      <option value="" selected disabled>Select Unit</option>
                      @if($unit->count())
                        @foreach($unit as $item)
                        <option value="{{$item->head_id}}" {{ $material['unit_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Unit</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Material Type</label>
                    <select class="form-control select2" name="material_type_id" required>
                      <option value="" selected disabled>Select Material Type</option>
                      @if($materialType->count())
                        @foreach($materialType as $item)
                          <option value="{{$item->head_id}}" {{ $material['material_type_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Material Type</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">          
                    <label>Current Vendor</label>
                    <select class="form-control select2" name="vendor_id" required id="vendor_id">
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ $material['vendor_id'] == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>File / Images</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="customFile" name="image[]" multiple>
                      <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                    <div class="valid-feedback" id="fileSuccess">Good job!</div>
                    <div class="invalid-feedback" id="fileError"></div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Material Location (In Store)</label>
                    <input type="text" class="form-control" name="location" value="{{$material['location']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Material Location</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Current Price</label>
                    <input type="number" step="0.001" class="form-control" name="cprice" required value="{{$material['cprice']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Price</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Opening Stock</label>
                    <input type="number" step="0.001" class="form-control" name="quantity" required value="{{$material['quantity'] ?? 0}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Stock</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$material['description']}}</textarea>
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
@endsection
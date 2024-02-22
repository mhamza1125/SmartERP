@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Vendor</h4>
            <div class="card-header-action">
              <a href="{{ route('vendor') }}" class="btn btn-primary">
                View All
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('vendor.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" required value="{{old('name')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Vendor Name</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{old('fname')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Full Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone1" required value="{{old('phone1')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Contact No</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Phone No</label>
                    <input type="text" class="form-control" name="phone2" value="{{old('phone2')}}">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Vendor Type</label>
                    <select class="form-control select2" name="vendor_type_id" required>
                      <option value="" selected disabled>Select Vendor Type</option>
                      @if($vendorType->count())
                        @foreach($vendorType as $item)
                          <option value="{{$item->head_id}}" {{ old('vendor_type_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor Type</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>City</label>
                    <select class="form-control select2" name="city_id" required>
                      <option value="" selected disabled>Select City</option>
                      @if($city->count())
                        @foreach($city as $item)
                          <option value="{{$item->head_id}}" {{ old('city_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select City</div>
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
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address" required>{{old('address')}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Address</div>
                  </div>
                </div>
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
@endsection
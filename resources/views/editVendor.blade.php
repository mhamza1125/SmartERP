@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Vendor</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('vendor.update', $vendor['vendor_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Vendor No</label>
                    <input type="hidden" name="vendor_type" value="0" required>
                    <input type="text" class="form-control" name="vendor_no" required value="{{$vendor['vendor_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Vendor No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" required value="{{$vendor['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Vendor Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{$vendor['fname']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Full Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone1" required value="{{$vendor['phone1']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Contact No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" class="form-control" name="cperson" value="{{$vendor['cperson']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Phone No</label>
                    <input type="text" class="form-control" name="phone2" value="{{$vendor['phone2']}}">
                    <div class="valid-feedback">Good job!</div>
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
                          <option value="{{$item->head_id}}" {{ $vendor['vendor_type_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                          <option value="{{$item->head_id}}" {{ $vendor['city_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Opening Balance</label>
                    <input type="number" class="form-control" name="credit" required value="{{old('credit') ?? '0'}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Balance</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Payable / Receiveable</label>
                    <select class="form-control" name="balance_type" required>
                      <option value="credit" {{ old('balance_type') == 'credit' ? 'selected' : '' }}>Payable</option>
                      <option value="debit" {{ old('balance_type') == 'debit' ? 'selected' : '' }}>Receiveable</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                {{-- <div class="col-md-4">
                  <div class="form-group">
                    <label>Vendor as Worker</label>
                    <select class="form-control" name="vendor_type" required>
                      <option value="0" {{ $vendor['vendor_type'] == '0' ? 'selected' : '' }}>Inactive</option>
                      <option value="1" {{ $vendor['vendor_type'] == '1' ? 'selected' : '' }}>Active</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div> --}}
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Vendor Materials</label>
                    <select class="form-control select2" name="material_id[]" multiple="">
                      <option value="" disabled>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" {{ $vmaterial->pluck('material_id')->contains($item->material_id) ? 'selected' : '' }}>{{$item->material_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Vendor Products</label>
                    <select class="form-control select2" name="product_id[]" multiple="">
                      <option value="" disabled>Select Product</option>
                      @if(isset($product) && $product->count())
                        @foreach($product as $item)
                          <option value="{{$item->product_id}}" {{ isset($vproduct) && $vproduct->pluck('product_id')->contains($item->product_id) ? 'selected' : '' }}>{{$item->article_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address" required>{{$vendor['address']}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Address</div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$vendor['description']}}</textarea>
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
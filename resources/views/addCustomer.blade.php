@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Customer</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('customer.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Customer No</label>
                    <input type="text" class="form-control" name="customer_no" required value="{{$count}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Customer No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{old('fname')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter First Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="lname" required value="{{old('lname')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Last Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" required value="{{old('email')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone" required value="{{old('phone')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Fax Number</label>
                    <input type="text" class="form-control" name="fax" required value="{{old('fax')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Country</label>
                    <select class="form-control select2" name="country_id" required>
                      <option value="" selected disabled>Select Country</option>
                      @if($country->count())
                        @foreach($country as $item)
                          <option value="{{$item->head_id}}" {{ old('country_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Country</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Currency</label>
                    <select class="form-control select2" name="currency_id" required>
                      <option value="" selected disabled>Select Currency</option>
                      @if($currency->count())
                        @foreach($currency as $item)
                          <option value="{{$item->head_id}}" {{ old('currency_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Currency</div>
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
                      <option value="debit" {{ old('balance_type') == 'debit' ? 'selected' : '' }}>Receiveable</option>
                      <option value="credit" {{ old('balance_type') == 'credit' ? 'selected' : '' }}>Payable</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address">{{old('address')}}</textarea>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" class="description">{{old('description')}}</textarea>
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

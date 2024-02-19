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
              <a href="{{ route('customer') }}" class="btn btn-primary">
                View All
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('customer.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Customer No</label>
                    <input type="text" class="form-control" name="customer_no" required value="{{old('customer_no')}}">
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
                    <input type="text" class="form-control" name="email" value="{{old('email')}}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone" value="{{old('phone')}}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Fax Number</label>
                    <input type="text" class="form-control" name="fax" value="{{old('fax')}}">
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Bank Account</h4>
            <div class="card-header-action">
              <a href="{{ route('bank') }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('bank.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Account Holder</label>
                    <select class="form-control select2" name="bank_holder" required>
                      <option value="admin" {{ old('bank_holder') == 'admin' ? 'selected' : '' }}>Admin / Self</option>
                      <option value="vendor" {{ old('bank_holder') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                      <option value="contractor" {{ old('bank_holder') == 'contractor' ? 'selected' : '' }}>Contractor</option>
                      <option value="employee" {{ old('bank_holder') == 'employee' ? 'selected' : '' }}>Employee</option>
                      <option value="customer" {{ old('bank_holder') == 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>             
                <div class="col-md-5" id="admin">
                  <div class="form-group">
                    <label>Opening Balance</label>
                    <input type="number" class="form-control" name="credit" id="credit" value="0">
                    <input type="hidden" name="banker_id" readonly value="0">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Balane</div>
                  </div>
                </div>
                <div class="col-md-5" id="employee">
                  <div class="form-group">          
                    <label>Employee</label>
                    <select class="form-control select2" name="banker_id">
                      <option value="" selected disabled>Select Employee</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option value="{{$item->employee_id}}" {{ old('banker_id') == $item->employee_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
                <div class="col-md-5" id="vendor">
                  <div class="form-group">          
                    <label>Vendor</label>
                    <select class="form-control select2" name="banker_id">
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ old('banker_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
                <div class="col-md-5" id="contractor">
                  <div class="form-group">          
                    <label>Contractor</label>
                    <select class="form-control select2" name="banker_id">
                      <option value="" selected disabled>Select Contractor</option>
                      @if($contractor->count())
                        @foreach($contractor as $item)
                          <option value="{{$item->vendor_id}}" {{ old('banker_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Contractor</div>
                  </div>
                </div>
                <div class="col-md-5" id="customer">
                  <div class="form-group">          
                    <label>Customer</label>
                    <select class="form-control select2" name="banker_id">
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ old('banker_id') == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Bank Types</label>
                    <select class="form-control select2" name="head_id" required>
                      <option value="" selected disabled>Select Bank Type</option>
                      @if($head->count())
                        @foreach($head as $item)
                          <option value="{{$item->head_id}}" {{ old('head_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Bank Type</div>
                  </div>
                </div>
              </div>  
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Account Title</label>
                    <input type="text" class="form-control" name="account_title" required value="{{old('account_title')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Account Title</div>
                  </div>
                </div>  
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bank Account</label>
                    <input type="text" class="form-control" name="account" required value="{{old('account')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Account No</div>
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
<script> var isBankPage = false; </script>
@endsection
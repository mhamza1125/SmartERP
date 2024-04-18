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
              <a href="{{ url()->previous() }}" class="btn btn-primary">
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
                      <option value="employee" {{ old('bank_holder') == 'employee' ? 'selected' : '' }}>Employee</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>             
                <div class="col-md-5" id="admin">                  
                  <div class="form-group">
                    <label>Admin / Self</label>
                    <input type="text" class="form-control" readonly value="Admin / Self">
                    <input type="hidden" name="banker_id" readonly value="0">
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
                          <option data-type="vendor" value="{{$item->vendor_id}}" {{ old('banker_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
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
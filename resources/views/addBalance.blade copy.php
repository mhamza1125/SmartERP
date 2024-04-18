@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Bank Balance</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <h4>Amount Receivng From</h4>
              <div class="row">
                <input type="hidden" name="payment_method" required value="bankBalance">
                <input type="hidden" name="transaction_type" required value="bankBalance">
                <input type="hidden" name="payment_method" required value="bankBalance">
                <input type="hidden" name="payee_bank_id" required value="0">
                <input type="hidden" name="payee_id" required value="0">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Transaction Coming From</label>
                    <select class="form-control select2" name="transaction_to" required>
                      <option value="cash" {{ old('transaction_to') == 'cash' ? 'selected' : '' }}>My Cash</option>
                      <option value="bank" {{ old('transaction_to') == 'bank' ? 'selected' : '' }}>My Bank Account</option>
                      <option value="customer" {{ old('transaction_to') == 'customer' ? 'selected' : '' }}>customer</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-5" id="cash">                  
                  <div class="form-group">
                    <label>My Cash</label>
                    <input type="text" class="form-control" readonly value="My Cash Balance">
                    <input type="hidden" name="payee_id" readonly value="0">
                  </div>
                </div>
                <div class="col-md-5" id="bank">
                  <div class="form-group">          
                    <label>My Bank</label>
                    <select class="form-control select2" name="payee_id" required>
                      <option value="" selected disabled>Select Bank</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->head_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
                <div class="col-md-5" id="customer">
                  <div class="form-group">          
                    <label>Customer</label>
                    <select class="form-control select2" name="payee_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ old('customer_id') == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
              </div>

              <h4>Amount Added To</h4>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Cash / Bank (if any)</label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="" selected disabled>Select Cash / Bank</option>
                      <option value="0">Cash Balance</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->head_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Bank</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" min="0" class="form-control" name="credit" required value="{{ old('credit') }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Transaction Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{old('transaction_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
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
<script>
  function toggleSections() {
      var selected = $('select[name="bank_holder"]').val();
      // Reset selects when not active
      if (selected != 'employee') {
          $('#employee select').val('').trigger('change');
      }if (selected != 'vendor') {
          $('#vendor select').val('').trigger('change');
      }
      // Hide all sections first
      $('#admin, #employee, #vendor').hide();
      if (selected == 'admin') {
          $('#admin').show();
      } else if (selected == 'employee') {
          $('#employee').show();
      } else if (selected == 'vendor') {
          $('#vendor').show();
      }
      // Re-initialize Select2 for visible select elements
      $('.select2:visible').select2();
  }
</script>
@endsection

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            {{-- Edit Pay Vendor / Contractor --}}
            <h4>Edit Pay Vendor</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.update', $transaction['transaction_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="hidden" name="transaction_to" required value="vendor" id="transaction_to">
                    <input type="hidden" name="debit" required value="0">
                    <label>Vendor</label>
                    <select class="form-control select2" name="payee_id" required id="payee_id">
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ $transaction['payee_id'] == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor / Contractor</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payment Type</label>
                    <select class="form-control" name="transaction_type" required id="transaction_type">
                      <option value="advance" {{ $transaction['transaction_type'] == 'advance' ? 'selected' : '' }}>Give Loan</option>
                      <option value="receiveAdvance" {{ $transaction['transaction_type'] == 'receiveAdvance' ? 'selected' : '' }}>Receive Loan</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6" id="display1">
                  <div class="form-group">
                    <label>Purchases / Processing</label>
                    <select class="form-control select2" name="order_id" id="order_id">
                      <option value="" disabled>Select Purchase / Processing</option>
                      {{-- Ajax Orders --}}
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Purchase / Processing</div>
                  </div>
                </div>
                <div class="col-md-6" id="display2">
                  <div class="form-group">
                    <label>Purchases</label>
                    <input type="text" readonly class="form-control" value="Not for Purchase / Processing">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Receiver Cash / Bank (if any)</label>
                    <select class="form-control select2" name="payee_bank_id" id="payee_bank_id" required>
                      <option value="0" selected>Cash Payment</option>
                      //Ajax Options 
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Cash / Bank</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payee Cash / Bank (if any)</label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="0" selected>Cash Payment</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ $transaction['bank_id'] == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Cash / Bank</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Pay Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{$transaction['transaction_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" min="0" class="form-control" name="credit" required value="{{$transaction['credit'] ?? $transaction['debit']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
                <div class="col-md-6">
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
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$transaction['description']}}</textarea>
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
  document.addEventListener('DOMContentLoaded', function () {
      var transactionTypeSelect = document.getElementById('transaction_type');
      var orderIdSelect = document.getElementById('order_id');
      var display1 = document.getElementById('display1');
      var display2 = document.getElementById('display2');

      // Function to update the display and required attribute
      function updateDisplay() {
          if (transactionTypeSelect.value === 'payment') {
              display1.style.display = 'block';
              display2.style.display = 'none';
              orderIdSelect.setAttribute('required', 'required');
          } else {
              display1.style.display = 'none';
              display2.style.display = 'block';
              orderIdSelect.removeAttribute('required');
          }
      }
      transactionTypeSelect.addEventListener('change', updateDisplay);
      updateDisplay();
  });

  var isPayPage = false;
  var isPayVendorPage = false;
  var ajaxBankUrl = "{{ route('ajaxBank') }}";
  var ajaxPurchaseUrl = "{{ route('ajaxPurchase') }}";
</script>
@endsection

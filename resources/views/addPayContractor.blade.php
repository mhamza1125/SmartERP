@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            {{-- Pay Vendor / Contractor --}}
            <h4>Pay Contractor</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="hidden" name="transaction_to" required value="contractor" id="transaction_to">
                    <input type="hidden" name="debit" required value="0">
                    <label>Contractor</label>
                    <select class="form-control select2" name="payee_id" required id="payee_id">
                      <option value="" selected disabled>Select Contractor</option>
                      @if($worker->count())
                        @foreach($worker as $item)
                          <option value="{{$item->vendor_id}}" {{ old('vendor_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Contractor</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payment Type</label>
                    <select class="form-control" name="transaction_type" required> {{-- id="transaction_type" --}}
                      <option value="advance" {{ old('transaction_type') == 'advance' ? 'selected' : '' }}>Give Loan</option>
                      <option value="receiveAdvance" {{ old('transaction_type') == 'receiveAdvance' ? 'selected' : '' }}>Receive Loan</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                {{-- <div class="col-md-6" id="display1">
                  <div class="form-group">
                    <label>Purchases / Processing</label>
                    <select class="form-control select2" name="order_id" id="order_id">
                      <option value="" disabled>Select Purchase / Processing</option>
                      {{-- Ajax Orders // That's commented
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
                </div> --}}
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
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payee Cash / Bank (if any)</label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="0" selected>Cash Payment</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Cash / Bank</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Outstanding Balance</label>
                    <input type="text" class="form-control" id="outstanding_balance" readonly placeholder="Select contractor to see balance">
                    <small class="form-text text-muted">Positive amount = We owe contractor</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payment Amount</label>
                    <input type="number" min="0" class="form-control" name="credit" required value="{{ old('credit') }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
              </div>
              <div class="row">
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
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Pay Date</label>
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
  // document.addEventListener('DOMContentLoaded', function () {
  //     var transactionTypeSelect = document.getElementById('transaction_type');
  //     var orderIdSelect = document.getElementById('order_id');
  //     var display1 = document.getElementById('display1');
  //     var display2 = document.getElementById('display2');

  //     // Function to update the display and required attribute
  //     function updateDisplay() {
  //         if (transactionTypeSelect.value === 'payment') {
  //             display1.style.display = 'block';
  //             display2.style.display = 'none';
  //             orderIdSelect.setAttribute('required', 'required');
  //         } else {
  //             display1.style.display = 'none';
  //             display2.style.display = 'block';
  //             orderIdSelect.removeAttribute('required');
  //         }
  //     }
  //     transactionTypeSelect.addEventListener('change', updateDisplay);
  //     updateDisplay();
  // });

  var isPayPage = true;
  var isPayVendorPage = true;
  var ajaxBankUrl = "{{ route('ajaxBank') }}";
  var ajaxPurchaseUrl = "{{ route('ajaxPurchase') }}";
  var ajaxBalanceUrl = "{{ route('ajaxBalance') }}";

  $(document).ready(function() {
    // Fetch balance when contractor is selected
    $('#payee_id').on('change', function() {
      var contractorId = $(this).val();
      if (contractorId) {
        $.ajax({
          url: ajaxBalanceUrl,
          type: "GET",
          data: { tableId: contractorId, table: 'vendor' },
          dataType: "json",
          success: function(response) {
            if (response.balance !== undefined) {
              var balance = parseFloat(response.balance);
              var balanceText = 'PKR ' + balance.toLocaleString();
              if (balance > 0) {
                balanceText += ' (We owe contractor)';
                $('#outstanding_balance').removeClass('text-danger').addClass('text-success');
              } else if (balance < 0) {
                balanceText += ' (Contractor owes us)';
                $('#outstanding_balance').removeClass('text-success').addClass('text-danger');
              } else {
                balanceText += ' (Balanced)';
                $('#outstanding_balance').removeClass('text-success text-danger');
              }
              $('#outstanding_balance').val(balanceText);
            } else {
              $('#outstanding_balance').val('Error loading balance');
            }
          },
          error: function() {
            $('#outstanding_balance').val('Error loading balance');
          }
        });
      } else {
        $('#outstanding_balance').val('');
      }
    });
  });
</script>
@endsection

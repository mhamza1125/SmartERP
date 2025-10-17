@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Payment</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <h6>Amount Receiving From</h6>
              <div class="row">
                <input type="hidden" name="transaction_to" required value="customer" id="transaction_to">
                <input type="hidden" name="transaction_type" required value="orderPayment">
                <input type="hidden" name="debit" required value="0">
                <div class="col-md-6">
                  <div class="form-group">          
                    <label>Customer</label>
                    <select class="form-control select2" name="payee_id" id="payee_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ old('customer_id') == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Sender Cash / Bank (if any)</label>
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
                    <label>Order</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      <option value="" selected disabled>Select Order</option>
                      {{-- Ajax Orders --}}
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Order</div>
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

              <h6>Amount Added To</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Cash / Bank (if any)</label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="" selected disabled>Select Cash / Bank</option>
                      <option value="0">Cash Balance</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Bank</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Outstanding Balance</label>
                    <input type="text" class="form-control" id="outstanding_balance" readonly placeholder="Select customer to see balance">
                    <small class="form-text text-muted">Positive amount = Customer owes us</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Net Amount Received <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="0.01" class="form-control" name="credit" id="credit" required value="{{ old('credit') }}">
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

              <!-- Payment Details Section -->
              <h6>Payment Details</h6>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Gross Amount (Customer Payment)</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="gross_amount" id="gross_amount" value="{{ old('gross_amount') }}" placeholder="Total amount customer paid">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Fees/Expenses</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="fees_expenses" id="fees_expenses" value="{{ old('fees_expenses') }}" placeholder="Bank fees, processing charges, etc.">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Net Amount (Auto-calculated)</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="net_amount" id="net_amount" value="{{ old('net_amount') }}" readonly placeholder="Gross - Fees">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="alert alert-info">
                    <h6>Payment Tracking Information:</h6>
                    <ul class="mb-0">
                      <li><strong>Gross Amount:</strong> Total amount the customer paid</li>
                      <li><strong>Fees/Expenses:</strong> Bank fees, processing charges, or other deductions</li>
                      <li><strong>Net Amount:</strong> Actual amount received after deducting fees (auto-calculated)</li>
                      <li><strong>Net Amount Received:</strong> Amount added to your bank/cash balance</li>
                    </ul>
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
  var isPayPage = false;
  var isPayOrderPage = false;
  var ajaxOrderUrl = "{{ route('ajaxOrder') }}";
  var ajaxBankUrl = "{{ route('ajaxBank') }}";

  // Auto-calculate net amount and sync with credit field
  function calculateNetAmount() {
    const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
    const feesExpenses = parseFloat(document.getElementById('fees_expenses').value) || 0;
    const netAmount = grossAmount - feesExpenses;

    document.getElementById('net_amount').value = netAmount.toFixed(2);
    document.getElementById('credit').value = netAmount.toFixed(2);
  }

  var ajaxBalanceUrl = "{{ route('ajaxBalance') }}";

  // Add event listeners when document is ready
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('gross_amount').addEventListener('input', calculateNetAmount);
    document.getElementById('fees_expenses').addEventListener('input', calculateNetAmount);

    // If credit is manually changed, update net_amount to match
    document.getElementById('credit').addEventListener('input', function() {
      const creditValue = parseFloat(this.value) || 0;
      document.getElementById('net_amount').value = creditValue.toFixed(2);
    });

    // Fetch balance when customer is selected
    $('#payee_id').on('change', function() {
      var customerId = $(this).val();
      if (customerId) {
        $.ajax({
          url: ajaxBalanceUrl,
          type: "GET",
          data: { tableId: customerId, table: 'customer' },
          dataType: "json",
          success: function(response) {
            if (response.balance !== undefined) {
              var balance = parseFloat(response.balance);
              var balanceText = 'PKR ' + balance.toLocaleString();
              if (balance > 0) {
                balanceText += ' (Customer owes us)';
                $('#outstanding_balance').removeClass('text-danger').addClass('text-success');
              } else if (balance < 0) {
                balanceText += ' (We owe customer)';
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

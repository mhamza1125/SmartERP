@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Order Payment</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.update', $transaction['transaction_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <h6>Amount Receiving From</h6>
              <div class="row">
                <input type="hidden" name="transaction_to" required value="customer" id="transaction_to">
                <input type="hidden" name="transaction_type" required value="orderPayment">
                <input type="hidden" name="credit" required value="0">
                <input type="hidden" name="payee_bank_id" required value="0">
                <div class="col-md-6">
                  <div class="form-group">          
                    <label>Customer</label>
                    <select class="form-control select2" name="payee_id" id="payee_id" required>
                      <option value="" selected disabled>Select Customer</option>
                      @if($customer->count())
                        @foreach($customer as $item)
                          <option value="{{$item->customer_id}}" {{ $transaction['payee_id'] == $item->customer_id ? 'selected' : '' }}>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Customer</div>
                  </div>
                </div>
                {{-- <div class="col-md-6">
                  <div class="form-group">
                    <label>Receiver Cash / Bank (if any)</label>
                    <select class="form-control select2" name="payee_bank_id" id="payee_bank_id" required>
                      <option value="0" selected>Cash Payment</option>
                      //Ajax Options 
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Cash / Bank</div>
                  </div>
                </div> --}}
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
                          <option value="{{$item->bank_id}}" {{ $transaction['bank_id'] == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Bank</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Net Amount Received <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="0.01" class="form-control" name="debit" id="debit" required value="{{ $transaction['debit'] }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Transaction Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{$transaction['transaction_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <!-- Payment Details Section -->
              <h6>Payment Details</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Fees/Expenses</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="fees_expenses" id="fees_expenses" value="{{ $transaction['fees_expenses'] ?? '' }}" placeholder="Bank fees, processing charges, etc.">
                    <div class="valid-feedback">Good job!</div>
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
  var isPayPage = false;
  var isPayOrderPage = false;
  var ajaxOrderUrl = "{{ route('ajaxOrder') }}";
  var ajaxBankUrl = "{{ route('ajaxBank') }}";
  var ajaxBalanceUrl = "{{ route('ajaxBalance') }}";

  // Add event listeners when document is ready
  document.addEventListener('DOMContentLoaded', function() {
    // Load balance on page load for edit mode
    var customerId = $('#payee_id').val();
    if (customerId) {
      loadCustomerBalance(customerId);
    }

    // Fetch balance when customer is selected
    $('#payee_id').on('change', function() {
      var customerId = $(this).val();
      if (customerId) {
        loadCustomerBalance(customerId);
      } else {
        $('#outstanding_balance').val('');
      }
    });

    function loadCustomerBalance(customerId) {
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
    }
  });
</script>
@endsection

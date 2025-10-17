@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Pay Employee</h4>
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
                    <input type="hidden" name="transaction_to" required value="employee" id="transaction_to">
                    <input type="hidden" name="credit" required value="0">
                    <label>Employee</label>
                    <select class="form-control select2" name="payee_id" required id="payee_id">
                      <option value="" selected disabled>Select Employee</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option value="{{$item->employee_id}}" {{ old('employee_id') == $item->employee_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}} {{($item->salary > 0)? ' | Salary: '.$item->salary : ' | Wages Employee'}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
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
                          <option value="{{$item->bank_id}}" {{ old('bank_id') == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}} - {{$item->account}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Cash / Bank</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Payment Type</label>
                    <select class="form-control" name="transaction_type" required>
                      <option value="salary" {{ old('transaction_type') == 'salary' ? 'selected' : '' }}>Salary</option>
                      <option value="salaryAdvance" {{ old('transaction_type') == 'salaryAdvance' ? 'selected' : '' }}>Salary Advance</option>
                      <option value="wages" {{ old('transaction_type') == 'wages' ? 'selected' : '' }}>Wages</option>
                      <option value="advance" {{ old('transaction_type') == 'advance' ? 'selected' : '' }}>Advance</option>
                      <option value="receiveAdvance" {{ old('transaction_type') == 'receiveAdvance' ? 'selected' : '' }}>Receive Advance</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Pay Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{old('transaction_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Outstanding Balance</label>
                    <input type="text" class="form-control" id="outstanding_balance" readonly placeholder="Select employee to see balance">
                    <small class="form-text text-muted">Positive amount = We owe employee</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payment Amount</label>
                    <input type="number" min="0" class="form-control" name="debit" required value="{{ old('debit') }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
              </div>
              <div class="row">
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
  var isPayPage = true;
  var ajaxBankUrl = "{{ route('ajaxBank') }}";
  var ajaxBalanceUrl = "{{ route('ajaxBalance') }}";

  $(document).ready(function() {
    // Fetch balance when employee is selected
    $('#payee_id').on('change', function() {
      var employeeId = $(this).val();
      if (employeeId) {
        $.ajax({
          url: ajaxBalanceUrl,
          type: "GET",
          data: { tableId: employeeId, table: 'employee' },
          dataType: "json",
          success: function(response) {
            if (response.balance !== undefined) {
              var balance = parseFloat(response.balance);
              var balanceText = 'PKR ' + balance.toLocaleString();
              if (balance > 0) {
                balanceText += ' (We owe employee)';
                $('#outstanding_balance').removeClass('text-danger').addClass('text-success');
              } else if (balance < 0) {
                balanceText += ' (Employee owes us)';
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

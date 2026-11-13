@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Journal Voucher</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="hidden" name="transaction_to" required value="generalVoucher">
                    <input type="hidden" name="transaction_type" required value="generalVoucher">
                    <input type="hidden" name="payee_bank_id" required value="0">
                    <input type="hidden" name="bank_id" required value="0">
                    <label>Payee Type</label>
                    <select class="form-control select2" name="payee_type" required>
                      <option value="" selected disabled>Select Payee Type</option>
                      <option value="vendor">Vendor</option>
                      <option value="contractor">Contractor</option>
                      <option value="employee">Employee</option>
                      <option value="customer">Customer</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Payee Type</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payee Name</label>
                    <!-- Vendor Payee -->
                    <div id="vendor">
                      <select class="form-control select2" name="payee_id">
                        <option value="" selected disabled>Select Vendor</option>
                        @if($vendor->count())
                          @foreach($vendor as $item)
                            <option value="{{$item->vendor_id}}">{{$item->vendor_no}} - {{$item->fname}}</option>
                          @endforeach
                        @endif
                      </select>
                    </div>

                    <!-- Contractor Payee -->
                    <div id="contractor">
                      <select class="form-control select2" name="payee_id">
                        <option value="" selected disabled>Select Contractor</option>
                        @if($contractor->count())
                          @foreach($contractor as $item)
                            <option value="{{$item->vendor_id}}">{{$item->vendor_no}} - {{$item->fname}}</option>
                          @endforeach
                        @endif
                      </select>
                    </div>

                    <!-- Employee Payee -->
                    <div id="employee">
                      <select class="form-control select2" name="payee_id">
                        <option value="" selected disabled>Select Employee</option>
                        @if($employee->count())
                          @foreach($employee as $item)
                            <option value="{{$item->employee_id}}">{{$item->employee_no}} - {{$item->name}}</option>
                          @endforeach
                        @endif
                      </select>
                    </div>

                    <!-- Customer Payee -->
                    <div id="customer">
                      <select class="form-control select2" name="payee_id">
                        <option value="" selected disabled>Select Customer</option>
                        @if($customer->count())
                          @foreach($customer as $item)
                            <option value="{{$item->customer_id}}">{{$item->customer_no}} - {{$item->fname}}</option>
                          @endforeach
                        @endif
                      </select>
                    </div>

                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Payee</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Voucher Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{old('transaction_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Voucher Type</label>
                    <select class="form-control select2" name="voucher_type" required>
                      <option value="" selected disabled>Select Voucher Type</option>
                      <option value="debit" {{ old('voucher_type') == 'debit' ? 'selected' : '' }}>Debit Voucher (Charge to Payee)</option>
                      <option value="credit" {{ old('voucher_type') == 'credit' ? 'selected' : '' }}>Credit Voucher (Credit to Payee)</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Voucher Type</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label id="amountLabel">Amount</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="amount" required value="{{ old('amount') }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description / Reason</label>
                    <textarea class="form-control" name="description" rows="4" required>{{old('description')}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Description</div>
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
var isGeneralVoucherPage = true;

$(document).ready(function() {
    // Update amount label based on voucher type
    function updateAmountLabel() {
        var voucherType = $('select[name="voucher_type"]').val();
        var label = $('#amountLabel');

        if (voucherType === 'debit') {
            label.text('Amount (Charge to Payee)');
        } else if (voucherType === 'credit') {
            label.text('Amount (Credit to Payee)');
        } else {
            label.text('Amount');
        }
    }

    // Update on page load
    updateAmountLabel();

    // Update on selection change
    $('select[name="voucher_type"]').change(function() {
        updateAmountLabel();
    });
});
</script>
@endsection


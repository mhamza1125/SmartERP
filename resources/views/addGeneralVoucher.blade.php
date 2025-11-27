@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add General Voucher</h4>
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
                    <label>Payee Type</label>
                    <select class="form-control select2" id="payeeType" name="payee_type" required>
                      <option value="" selected disabled>Select Payee Type</option>
                      <option value="vendor">Vendor</option>
                      <option value="contractor">Contractor</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Payee Type</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Payee Name</label>
                    <select class="form-control select2" id="payeeSelect" name="payee_id" required>
                      <option value="" selected disabled>Select Payee</option>
                    </select>
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
                    <label>Amount (Charge to Payee)</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="debit" required value="{{ old('debit') }}">
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
document.getElementById('payeeType').addEventListener('change', function() {
  const payeeType = this.value;
  const payeeSelect = document.getElementById('payeeSelect');
  payeeSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
  
  if (payeeType) {
    fetch(`/ajaxPayee?type=${payeeType}`)
      .then(response => response.json())
      .then(data => {
        payeeSelect.innerHTML = '<option value="" selected disabled>Select Payee</option>';
        data.forEach(item => {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = `${item.no} - ${item.name}`;
          payeeSelect.appendChild(option);
        });
      })
      .catch(error => {
        console.error('Error:', error);
        payeeSelect.innerHTML = '<option value="" selected disabled>Error loading payees</option>';
      });
  }
});
</script>
@endsection


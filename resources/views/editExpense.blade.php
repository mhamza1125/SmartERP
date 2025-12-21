@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Expense</h4>
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
                    <input type="hidden" name="transaction_to" required value="expense">
                    <input type="hidden" name="payment_method" required value="expense">
                    <input type="hidden" name="transaction_type" required value="expense">
                    <input type="hidden" name="payee_bank_id" required value="0">
                    <label>Expense</label>
                    <select class="form-control select2" name="payee_id" required>
                      <option value="" selected disabled>Select Expense Head</option>
                      @if($expense->count())
                        @foreach($expense as $item)
                          <option value="{{$item->head_id}}" {{ $transaction['payee_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Expense Head</div>
                  </div>
                </div>
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
                    <label>Expense Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{$transaction['transaction_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Entry Type</label>
                    @php
                      $isDebit = isset($transaction['debit']) && $transaction['debit'] > 0;
                      $expenseType = $isDebit ? 'debit' : 'credit';
                    @endphp
                    <div class="custom-control custom-radio">
                      <input type="radio" id="creditRadio" name="expense_type" class="custom-control-input" value="credit" {{ $expenseType === 'credit' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="creditRadio">
                        Expense Incurred (Debit)
                      </label>
                    </div>
                    <div class="custom-control custom-radio">
                      <input type="radio" id="debitRadio" name="expense_type" class="custom-control-input" value="debit" {{ $expenseType === 'debit' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="debitRadio">
                        Expense Reversal (Credit)
                      </label>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Cash / Bank (if any)</label>
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
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="amount" required value="{{$transaction['credit'] ?? $transaction['debit']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
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
@endsection

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Balance Adjustment - Bank / Cash Balance Adjustment</h4>
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
                <div class="col-md-5">
                  <div class="form-group">
                    <input type="hidden" name="transaction_to" required value="{{ $transaction['transaction_to'] ?? 'brs' }}">
                    <input type="hidden" name="transaction_type" required value="{{ $transaction['transaction_type'] ?? 'brs' }}">
                    <input type="hidden" name="payee_bank_id" required value="0">
                    <label>Cash / Bank Balance Adjustment</label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="0" selected>Cash Balance</option>
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
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" min="0" class="form-control" name="debit" required value="{{ $transaction['debit'] ?? $transaction['credit'] }}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Adjustment Type</label>
                    <select class="form-control" name="brs_type" required>
                      <option value="1" {{ isset($transaction['credit']) ? 'selected' : '' }}>Increase</option>
                      <option value="2" {{ isset($transaction['debit']) ? 'selected' : '' }}>Deduct</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Adjustment Type</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Adjustment Date</label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{$transaction['transaction_date']}}">
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
@endsection

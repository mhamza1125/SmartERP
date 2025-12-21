@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank/Cash Transfer</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('transaction.storeTransfer') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>From Account <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="from_bank_id" required>
                      <option value="">-- Select Account --</option>
                      <option value="0" {{ old('from_bank_id') == '0' ? 'selected' : '' }}>Cash Balance</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('from_bank_id') == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="invalid-feedback">Select From Account</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>To Account <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="to_bank_id" required>
                      <option value="">-- Select Account --</option>
                      <option value="0" {{ old('to_bank_id') == '0' ? 'selected' : '' }}>Cash Balance</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{$item->bank_id}}" {{ old('to_bank_id') == $item->bank_id ? 'selected' : '' }}>{{$item->hname}} - {{$item->account_title}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="invalid-feedback">Select To Account</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Amount <span class="text-danger">*</span></label>
                    <input type="number" min="0.01" step="0.01" class="form-control" name="amount" required value="{{ old('amount') }}">
                    <div class="invalid-feedback">Enter Amount</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Transfer Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker" name="transaction_date" required value="{{old('transaction_date')}}">
                    <div class="invalid-feedback">Select Date</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Check Number (Optional)</label>
                    <input type="text" class="form-control" name="check_no" value="{{ old('check_no') }}">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description/Notes</label>
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
@endsection


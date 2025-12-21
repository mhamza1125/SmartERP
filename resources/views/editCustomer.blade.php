@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Customer</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('customer.update', $customer['customer_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Customer No</label>
                    <input type="text" class="form-control" name="customer_no" required value="{{$customer['customer_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Customer No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{$customer['fname']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter First Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="lname" required value="{{$customer['lname']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Last Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" value="{{$customer['email']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone" value="{{$customer['phone']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Fax Number</label>
                    <input type="text" class="form-control" name="fax" value="{{$customer['fax']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Country</label>
                    <select class="form-control select2" name="country_id" required>
                      <option value="" selected disabled>Select Country</option>
                      @if($country->count())
                        @foreach($country as $item)
                          <option value="{{$item->head_id}}" {{ $customer['country_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Country</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Currency</label>
                    <select class="form-control select2" name="currency_id" required>
                      <option value="" selected disabled>Select Currency</option>
                      @if($currency->count())
                        @foreach($currency as $item)
                          <option value="{{$item->head_id}}" {{ $customer['currency_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Currency</div>
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
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Opening Balance</label>
                    @php
                      $obAmount = 0;
                      $obType = 'credit';
                      if ($openingBalance) {
                        $obAmount = $openingBalance->debit ?? $openingBalance->credit ?? 0;
                        $obType = $openingBalance->debit ? 'debit' : 'credit';
                      }
                    @endphp
                    <input type="number" class="form-control" name="credit" required value="{{old('credit') ?? $obAmount}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Balance</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Payable / Receiveable</label>
                    <select class="form-control" name="balance_type" required>
                      <option value="credit" {{ (old('balance_type') ?? $obType) == 'credit' ? 'selected' : '' }}>Receiveable</option>
                      <option value="debit" {{ (old('balance_type') ?? $obType) == 'debit' ? 'selected' : '' }}>Payable</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address">{{$customer['address']}}</textarea>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$customer['description']}}</textarea>
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
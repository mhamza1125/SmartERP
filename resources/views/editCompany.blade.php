@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Company</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('company.update', $company->id) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" class="form-control" name="name" required value="{{old('name', $company->name)}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Company Name</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>CEO</label>
                    <input type="text" class="form-control" name="ceo" required value="{{old('ceo', $company->ceo)}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter CEO Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Phone</label>
                    <input type="text" class="form-control" name="phone" required value="{{old('phone', $company->phone)}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Phone Number</div>
                  </div>
                </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email', $company->email) }}" required>
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>NTN</label>
                                            <input type="text" class="form-control" name="ntn" value="{{ old('ntn', $company->ntn) }}">
                                            @error('ntn')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="text" class="form-control" name="website" value="{{ old('website', $company->website) }}">
                                            @error('website')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" name="address" rows="3">{{ old('address', $company->address) }}</textarea>
                                            @error('address')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input type="text" class="form-control" name="city" value="{{ old('city', $company->city) }}">
                                            @error('city')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <input type="text" class="form-control" name="country" value="{{ old('country', $company->country) }}">
                                            @error('country')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>ZIP Code</label>
                                            <input type="text" class="form-control" name="zip" value="{{ old('zip', $company->zip) }}">
                                            @error('zip')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fax</label>
                                            <input type="text" class="form-control" name="fax" value="{{ old('fax', $company->fax) }}">
                                            @error('fax')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Logo Upload</label>
                                            <input type="file" class="form-control" name="logo" accept="image/*">
                                            @if($company->logo_path)
                                                <small class="text-muted">Current logo: {{ $company->logo_path }}</small>
                                            @endif
                                            @error('logo')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Footer Text</label>
                                            <textarea class="form-control" name="footer_text" rows="3">{{ old('footer_text', $company->footer_text) }}</textarea>
                                            @error('footer_text')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update Company</button>
                                    <a href="{{ route('company') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

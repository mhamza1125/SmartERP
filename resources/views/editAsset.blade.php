@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Asset</h4>
          </div>
          <div class="card-body">
            @if($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            <form action="{{ route('asset.update', $asset->asset_id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Asset Name <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <input type="text" class="form-control @error('asset_name') is-invalid @enderror" name="asset_name" value="{{ old('asset_name', $asset->asset_name) }}" required>
                  @error('asset_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Transaction Type <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <select class="form-control @error('transaction_type') is-invalid @enderror" name="transaction_type" required>
                    <option value="">Select Transaction Type</option>
                    <option value="purchase" {{ old('transaction_type', $asset->transaction_type) == 'purchase' ? 'selected' : '' }}>Purchase (Acquisition)</option>
                    <option value="sale" {{ old('transaction_type', $asset->transaction_type) == 'sale' ? 'selected' : '' }}>Sale (Disposal)</option>
                    <option value="depreciation" {{ old('transaction_type', $asset->transaction_type) == 'depreciation' ? 'selected' : '' }}>Depreciation</option>
                    <option value="writeoff" {{ old('transaction_type', $asset->transaction_type) == 'writeoff' ? 'selected' : '' }}>Write-off</option>
                    <option value="adjustment" {{ old('transaction_type', $asset->transaction_type) == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                  </select>
                  @error('transaction_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Transaction Date <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" name="transaction_date" value="{{ old('transaction_date', $asset->transaction_date?->format('Y-m-d')) }}" required>
                  @error('transaction_date')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Amount <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $asset->debit ?? $asset->credit) }}" min="0" required>
                  @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Description</label>
                <div class="col-sm-12 col-md-7">
                  <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $asset->description) }}</textarea>
                  @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Attachment</label>
                <div class="col-sm-12 col-md-7">
                  @if($asset->attachment)
                    <div class="mb-2">
                      <a href="{{ asset('storage/' . $asset->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-download"></i> Current File
                      </a>
                    </div>
                  @endif
                  <input type="file" class="form-control @error('attachment') is-invalid @enderror" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                  <small class="form-text text-muted">PDF, JPG, JPEG, PNG (Max 2MB) - Leave empty to keep current</small>
                  @error('attachment')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                <div class="col-sm-12 col-md-7">
                  <button type="submit" class="btn btn-primary">Update Transaction</button>
                  <a href="{{ route('asset.show', $asset->asset_name) }}" class="btn btn-secondary">Cancel</a>
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


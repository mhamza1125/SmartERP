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
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Quantity <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <input type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', $asset->quantity) }}" min="1" required>
                  @error('quantity')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Amount <span class="text-danger">*</span></label>
                <div class="col-sm-12 col-md-7">
                  <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $asset->amount) }}" min="0" required>
                  @error('amount')
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
                  <button type="submit" class="btn btn-primary">Update</button>
                  <a href="{{ route('asset.show', $asset->asset_id) }}" class="btn btn-secondary">Cancel</a>
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


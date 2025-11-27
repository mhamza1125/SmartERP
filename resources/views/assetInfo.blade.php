@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Asset Details</h4>
            <div class="card-header-action">
              <a href="{{ route('asset.edit', $asset->asset_id) }}" class="btn btn-primary">Edit</a>
              <a href="{{ route('asset') }}" class="btn btn-secondary">Back</a>
            </div>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label><strong>Asset Name</strong></label>
                  <p>{{ $asset->asset_name }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label><strong>Quantity</strong></label>
                  <p>{{ $asset->quantity }}</p>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label><strong>Amount</strong></label>
                  <p>{{ number_format($asset->amount, 2) }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label><strong>Created Date</strong></label>
                  <p>{{ $asset->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
              </div>
            </div>
            @if($asset->attachment)
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label><strong>Attachment</strong></label>
                    <p>
                      <a href="{{ asset('storage/' . $asset->attachment) }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fas fa-download"></i> Download
                      </a>
                    </p>
                  </div>
                </div>
              </div>
            @endif
            <div class="row">
              <div class="col-md-12">
                <form action="{{ route('asset.destroy', $asset->asset_id) }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection


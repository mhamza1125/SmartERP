{{-- Edit an asset / liability entry ($register from AssetController / LiabilityController) --}}
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit {{ $register['title'] }} Entry #{{ $entry->asset_id }}</h4>
          </div>
          <div class="card-body">
            <form action="{{ route($register['route'] . '.update', $entry->asset_id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @include('assetForm')
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                <div class="col-sm-12 col-md-7">
                  <button type="submit" class="btn btn-primary">Update Entry</button>
                  <a href="{{ route($register['route'] . '.show', ['name' => $entry->asset_name]) }}" class="btn btn-secondary">Cancel</a>
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

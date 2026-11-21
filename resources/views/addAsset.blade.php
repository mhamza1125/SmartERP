{{-- Add an asset / liability entry ($register from AssetController / LiabilityController) --}}
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add {{ $register['title'] }} Entry</h4>
          </div>
          <div class="card-body">
            <form action="{{ route($register['route'] . '.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              @include('assetForm')
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                <div class="col-sm-12 col-md-7">
                  <button type="submit" class="btn btn-primary">Save Entry</button>
                  <a href="{{ $presetName !== '' ? route($register['route'] . '.show', ['name' => $presetName]) : route($register['route']) }}" class="btn btn-secondary">Cancel</a>
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

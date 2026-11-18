@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Head</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('head') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('head.headType') }}" class="btn btn-primary">Head Types</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('head.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Head Type</label>
                <div class="col-sm-12 col-md-7">
                  <select class="form-control select2" name="head_type_id" required>
                    <option value="" selected disabled>Choose Head Type</option>
                    @if($headType->count())
                      @foreach($headType as $item)
                        <option value="{{$item->head_type_id}}" {{ old('head_type_id') == $item->head_type_id ? 'selected' : '' }}>{{$item->name}}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Title</label>
                <div class="col-sm-12 col-md-7">
                  <input type="text" name="name" class="form-control" placeholder="Head Title" required value="{{ old('name') }}">
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Head </div>
                </div>
              </div>
              {{-- Departments (head type 3) only: working hours of the department --}}
              <div class="form-group row mb-4 js-department-times" style="display:none">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Working Hours</label>
                <div class="col-sm-6 col-md-3">
                  <input type="time" name="work_start_time" class="form-control" value="{{ old('work_start_time') }}" title="Work start time">
                  <small class="form-text text-muted">Start (late time after this)</small>
                </div>
                <div class="col-sm-6 col-md-3">
                  <input type="time" name="work_end_time" class="form-control" value="{{ old('work_end_time') }}" title="Work end time">
                  <small class="form-text text-muted">End (overtime after this)</small>
                </div>
              </div>
              <div class="form-group row mb-4">
                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                <div class="col-sm-12 col-md-7">
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
@push('scripts')
<script>
  $(function () {
    var toggle = function () { $('.js-department-times').toggle($('select[name="head_type_id"]').val() === '3'); };
    $('select[name="head_type_id"]').on('change', toggle);
    toggle();
  });
</script>
@endpush
@endsection
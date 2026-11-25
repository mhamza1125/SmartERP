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
              {{-- Departments (head type 3) only: working hours and break of the department --}}
              <div class="js-department-times js-department-hours" style="display:none">
                <div class="form-group row mb-4">
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
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Break Duration</label>
                  <div class="col-sm-6 col-md-2">
                    <div class="input-group">
                      <input type="number" name="break_hours" class="form-control" min="0" max="23" step="1" placeholder="0" value="{{ old('break_hours') }}">
                      <div class="input-group-append"><span class="input-group-text">h</span></div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-md-2">
                    <div class="input-group">
                      <input type="number" name="break_mins" class="form-control" min="0" max="59" step="1" placeholder="0" value="{{ old('break_mins') }}">
                      <div class="input-group-append"><span class="input-group-text">min</span></div>
                    </div>
                  </div>
                  <div class="col-12 offset-md-3 col-md-7">
                    <small class="form-text js-working-hours text-muted"></small>
                    <small class="form-text text-muted">Payroll hours per day = start to end minus the break (e.g. 9:00-18:00 with a 1 h break = 8 h).</small>
                  </div>
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

  // Department forms: actual working hours = Work In to Work Out (end before start = next day) minus the break
  $(document).on('input change', '.js-department-hours input', function () {
    var $box = $(this).closest('.js-department-hours');
    var start = $box.find('[name="work_start_time"]').val(), end = $box.find('[name="work_end_time"]').val();
    var brk = (parseInt($box.find('[name="break_hours"]').val(), 10) || 0) * 60 + (parseInt($box.find('[name="break_mins"]').val(), 10) || 0);
    var $out = $box.find('.js-working-hours').removeClass('text-danger text-success');
    if (!start || !end) { $out.text('Enter the start and end time to see the working hours.'); return; }
    var toMin = function (t) { var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10); };
    var span = toMin(end) - toMin(start);
    if (span <= 0) span += 24 * 60; // night shift
    var work = span - brk;
    var fmt = function (m) { var h = Math.floor(m / 60), r = m % 60; return h + ' h' + (r ? ' ' + r + ' min' : ''); };
    if (work <= 0) { $out.addClass('text-danger').text('The break must be shorter than ' + fmt(span) + '.'); return; }
    $out.addClass('text-success').text('Total ' + fmt(span) + ' - break ' + fmt(brk) + ' = ' + fmt(work) + ' actual working hours (' + (Math.round(work / 60 * 100) / 100) + ' h/day)');
  });
  $('.js-department-hours').each(function () { $(this).find('input').first().trigger('change'); });
  });
</script>
@endpush
@endsection
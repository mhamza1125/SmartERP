@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Heads Table</h4>
            <div class="card-header-action">
              <div class="btn-group">
                @can('create', App\Models\Head::class)<a href="{{ route('head.add') }}" class="btn btn-primary">Add Head</a>@endcan
                <a href="{{ route('head.headType') }}" class="btn btn-primary">Head Types</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <!-- First Tab for All Heads -->
              <li class="nav-item">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">All Heads</a>
              </li>
              <!-- Create a Tab for Each Head Type -->
              @foreach($headType as $ht)
                <li class="nav-item">
                  <a class="nav-link" id="type-{{$ht->head_type_id}}-tab" data-toggle="tab" href="#type-{{$ht->head_type_id}}" role="tab" aria-controls="type-{{$ht->head_type_id}}" aria-selected="false">{{$ht->name}}</a>
                </li>
              @endforeach
            </ul>

            <div class="tab-content" id="myTabContent">
              <!-- Tab Content for All Heads -->
              <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="table-responsive mt-3">
                  <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Head Type</th>
                        <th>Head</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($head->count())
                        @foreach($head as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->htname}}</td>
                          <td>{{$item->name}}</td>
                          <td>
                            @if($item->head_status)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                          <td>
                            @can('edit', App\Models\Head::class)<button type="button" {{($item->action == 1)? 'disabled':''}} class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->head_id}}">Edit</button>@endcan
@if($item->action != 1)@can('delete', App\Models\Head::class)@include('deleteButton', ['url' => route('head.destroy', $item->head_id), 'check' => route('head.deleteCheck', $item->head_id), 'label' => 'Head '.$item->name, 'class' => 'btn btn-danger btn-sm'])@endcan @endif
                          </td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Tab Content for Each Head Type -->
              @foreach($headType as $ht)
              <div class="tab-pane fade" id="type-{{$ht->head_type_id}}" role="tabpanel" aria-labelledby="type-{{$ht->head_type_id}}-tab">
                <div class="table-responsive mt-3">
                  <table class="table table-striped table-hover" id="tableExportType{{$ht->head_type_id}}" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Head</th>
                        @if($ht->head_type_id == 3)<th>Work Start</th><th>Work End</th><th>Break</th><th>Working Hours</th>@endif
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $filteredHeads = $head->filter(function($item) use ($ht) {
                          return $item->head_type_id == $ht->head_type_id;
                        });
                      @endphp
                      @if($filteredHeads->count())
                        @foreach($filteredHeads as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->name}}</td>
                          @if($ht->head_type_id == 3)
                            {{-- Department working hours (late time from the start, overtime after the end) --}}
                            <td>{{ $item->work_start_time ? \Carbon\Carbon::parse($item->work_start_time)->format('h:i A') : '-' }}</td>
                            <td>{{ $item->work_end_time ? \Carbon\Carbon::parse($item->work_end_time)->format('h:i A') : '-' }}</td>
                            <td>{{ $item->work_start_time ? App\Repositories\HeadRepository::breakLabel($item->break_minutes) : '-' }}</td>
                            @php $workHours = App\Repositories\HeadRepository::workingHours($item->work_start_time, $item->work_end_time, $item->break_minutes); @endphp
                            <td>
                              @if($workHours)
                                {{ rtrim(rtrim(number_format($workHours, 2), '0'), '.') }} h/day
                              @else
                                <span class="text-danger small" title="Payroll cannot work out an hourly wage for this department's employees">Not set</span>
                              @endif
                            </td>
                          @endif
                          <td>
                            @if($item->head_status)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                          <td>
                            @can('edit', App\Models\Head::class)<button type="button" {{($item->action == 1)? 'disabled':''}} class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->head_id}}">Edit</button>@endcan
@if($item->action != 1)@can('delete', App\Models\Head::class)@include('deleteButton', ['url' => route('head.destroy', $item->head_id), 'check' => route('head.deleteCheck', $item->head_id), 'label' => 'Head '.$item->name, 'class' => 'btn btn-danger btn-sm'])@endcan @endif
                          </td>
                        </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="{{ $ht->head_type_id == 3 ? 8 : 4 }}" class="text-center">No heads available for this type</td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal for Editing Heads -->
@if($head->count())
  @foreach($head as $item)
    <div class="modal fade" id="exampleModal{{$item->head_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Head</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('head.update', $item->head_id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="card-body">
                <div class="form-group">
                  <label>Head Name</label>
                  <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Head Name</div>
                </div>
                <div class="form-group">
                  <label>Head Type</label>
                  <select class="form-control" name="head_type_id" required="">
                    <option value="" selected disabled>Choose Head Type</option>
                    @foreach($headType as $ht)
                      <option value="{{ $ht->head_type_id }}" {{ $item->head_type_id == $ht->head_type_id ? 'selected' : '' }}>{{ $ht->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label>Head Status</label>
                  <select class="form-control" name="head_status" required="">
                    <option value="" selected disabled>Choose Head Status</option>
                    <option value="1" {{ $item->head_status == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $item->head_status == '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                </div>
                @if($item->head_type_id == 3)
                  {{-- Department working hours: late time is counted from the start, overtime after the end --}}
                  <div class="js-department-hours">
                  <div class="form-row">
                    <div class="form-group col-6">
                      <label>Work Start Time</label>
                      <input type="time" class="form-control" name="work_start_time" value="{{ $item->work_start_time ? substr($item->work_start_time, 0, 5) : '' }}">
                    </div>
                    <div class="form-group col-6">
                      <label>Work End Time</label>
                      <input type="time" class="form-control" name="work_end_time" value="{{ $item->work_end_time ? substr($item->work_end_time, 0, 5) : '' }}">
                    </div>
                  </div>
                  <label>Break Duration</label>
                  <div class="form-row">
                    <div class="form-group col-6 mb-1">
                      <div class="input-group">
                        <input type="number" class="form-control" name="break_hours" min="0" max="23" step="1" placeholder="0" value="{{ intdiv((int) $item->break_minutes, 60) ?: '' }}">
                        <div class="input-group-append"><span class="input-group-text">h</span></div>
                      </div>
                    </div>
                    <div class="form-group col-6 mb-1">
                      <div class="input-group">
                        <input type="number" class="form-control" name="break_mins" min="0" max="59" step="1" placeholder="0" value="{{ ((int) $item->break_minutes % 60) ?: '' }}">
                        <div class="input-group-append"><span class="input-group-text">min</span></div>
                      </div>
                    </div>
                  </div>
                  <small class="form-text js-working-hours text-muted"></small>
                  </div>
                  <small class="form-text text-muted">Used for late time / overtime, and payroll hours per day = start to end minus the break. Without times, no late time / overtime and no hourly wage.</small>
                @endif
              </div>
              <div class="card-footer text-right">
                <button class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif
@push('scripts')
<script>
  $(function () {
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

{{-- Attendance machine database problems and employee mapping warnings (attendance + salary calculation pages) --}}
@if(!empty($machineError))
  <div class="alert alert-danger">
    <b>Attendance records could not be read.</b> {{ $machineError }}
    @can('edit', App\Models\Settings::class)
      <br>The file location is set under <a href="{{ route('company') }}" class="alert-link">Settings &gt; Company</a>.
    @endcan
  </div>
@elseif(isset($machineDb) && !$machineDb['ok'])
  <div class="alert alert-warning">
    {{ $machineDb['message'] }}
    @can('edit', App\Models\Settings::class)
      Set the file location under <a href="{{ route('company') }}" class="alert-link">Settings &gt; Company</a>.
    @endcan
  </div>
@endif
@if(!empty($warnings))
  <div class="alert alert-light border">
    @foreach($warnings as $warning)
      <div>&bull; {{ $warning }}</div>
    @endforeach
  </div>
@endif

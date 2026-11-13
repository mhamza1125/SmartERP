@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Machine Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Machine::class)<a href="{{ route('machine.add') }}" class="btn btn-primary">Add Machine</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Current Employee</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($machine->count())
                    @foreach($machine as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->machine_no}}</td>
                      <td>{{$item->hname}}</td>
                      <td>{{$item->name ? $item->employee_no .' - '. $item->name : 'Not Asigned'}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Machine::class))<a href="{{ route('machine.show', $item->machine_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Machine::class)<a href="{{ route('machine.edit', $item->machine_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Machine::class)@include('deleteButton', ['url' => route('machine.destroy', $item->machine_id), 'check' => route('machine.deleteCheck', $item->machine_id), 'label' => 'Machine '.$item->machine_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Current Employee</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Employee Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Employee::class)<a href="{{ route('employee.add') }}" class="btn btn-primary">Add Employee</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee No</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>City</th>
                    <th>Employee Type</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($employee->count())
                    @foreach($employee as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->employee_no}}</td>
                      <td>{{$item->name}}</td>                      
                      <td>{{$item->dname}}</td>                     
                      <td>{{$item->designation}}</td>                      
                      <td>{{$item->cname}}</td>
                      <td>@if($item->employee_type_id == 39) 
                        <span class="badge badge-success">Salary</span> 
                        @elseif($item->employee_type_id == 40) 
                        <span class="badge badge-primary">Wages</span>
                        @else 
                        <span class="badge badge-secondary">Other</span>
                        @endif
                      </td>
                      <td>
                        @if($item->employee_status) 
                        <span class="badge badge-success">Active</span> @else 
                        <span class="badge badge-danger">Inactive</span> @endif
                      </td>                       
                      <td>
                        @if(auth()->user()->can('show', App\Models\Employee::class))<a href="{{ route('employee.show', $item->employee_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Employee::class)<a href="{{ route('employee.edit', $item->employee_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Employee::class)@include('deleteButton', ['url' => route('employee.destroy', $item->employee_id), 'check' => route('employee.deleteCheck', $item->employee_id), 'label' => 'Employee '.$item->employee_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                        @if(auth()->user()->can('show', App\Models\Employee::class) && auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('employee.detail', $item->employee_id) }}" class="btn btn-success btn-sm">Ledger</a>@endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee No</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>City</th>
                    <th>Employee Type</th>
                    <th>Status</th>
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

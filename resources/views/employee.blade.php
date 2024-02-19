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
              <a href="{{ route('employee.add') }}" class="btn btn-primary">Add Employee</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee No</th>
                    <th>Employee Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($employee->count())
                    @foreach($employee as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{$item->employee_no}}</td>
                      <td>{{$item->fname}} {{$item->lname}}</td>                      
                      <td>
                        <a href="{{ route('employee.show', $item->employee_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('employee.edit', $item->employee_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee No</th>
                    <th>Employee Name</th>
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
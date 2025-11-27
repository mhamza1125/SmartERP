@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Employee Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('employee.print', $employee['employee_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('employee') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('employee.edit', $employee['employee_id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('employee.detail', $employee['employee_id']) }}" class="btn btn-primary">Ledger</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Employee No: </b> {{$employee['employee_no']}}</td>
                  <td><b>Department: </b> {{$employee['dname']}}</td>
                  <td><b>Type: </b> {{$employee['etname']}}</td>
                </tr>
                <tr>
                  <td><b>Name: </b> {{$employee['name']}}</td>
                  <td><b>Short Name: </b> {{$employee['sname']}}</td>
                  <td><b>Father Name: </b> {{$employee['fname']}}</td>
                </tr>
                <tr>
                  <td><b>CNIC: </b> {{$employee['cnic']}}</td>
                  <td><b>Contact No: </b> {{$employee['phone1']}}</td>
                  <td><b>Emergency No: </b> {{$employee['phone2']}}</td>
                </tr>
                <tr>
                  <td><b>City: </b> {{$employee['cname']}}</td>
                  <td><b>Current Status: </b> @if($employee['employee_status']) 
                    <span class="badge badge-success">Active</span> @else 
                    <span class="badge badge-danger">Inactive</span> @endif</td>
                  <td><b>Joining Date: </b> {{$employee['joining_date']}}</td>
                </tr>
                <tr>
                  <td><b>Current Salary: </b> Rs. {{number_format($employee['salary'])}} </td>
                  <td><b>Attendance No: </b> {{$employee['attendance_id']}}</td>
                  <td><b>Address: </b> {{$employee['address']}}, {{$employee['cname']}}</td>
                </tr>
                <tr>
                  <td><b>Designation: </b> {{$employee['designation']}} </td>
                  <td colspan="2">
                    <div class="row">
                      <div class="col-md-2"><b>Details: </b></div>
                      <div class="col-md-10">
                        @php echo $employee['description'] @endphp
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            @if($image->count())
              <h5>Images</h5>
              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                @foreach($image as $item)
                  <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <a href="{{ URL::asset('resources/employee/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/employee/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'employee'])}}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                    </form>
                  </div>
                @endforeach
              </div>
            @else
              <blockquote> No Images </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
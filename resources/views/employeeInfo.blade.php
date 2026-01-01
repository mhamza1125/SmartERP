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
                  <td><b>Joining Date: </b> {{\Carbon\Carbon::parse($employee['joining_date'])->format('d-m-Y')}}</td>
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

            <!-- Personal Information Section -->
            @if($employee['marital_status'] || $employee['siblings_count'])
              <h5 class="mt-4 mb-3">Personal Information</h5>
              <table class="table">
                <tbody>
                  @if($employee['marital_status'])
                    <tr>
                      <td><b>Marital Status: </b> {{ ucfirst($employee['marital_status']) }}</td>
                    </tr>
                  @endif
                  @if($employee['siblings_count'])
                    <tr>
                      <td><b>Number of Siblings: </b> {{ $employee['siblings_count'] }}</td>
                    </tr>
                  @endif
                </tbody>
              </table>
            @endif

            <!-- Children Details Section -->
            @if($employee['children_details'] && count($employee['children_details']) > 0)
              <h5 class="mt-4 mb-3">Children Details</h5>
              <table class="table table-bordered table-striped">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee['children_details'] as $child)
                    @if(!empty($child['name']))
                      <tr>
                        <td>{{ $child['name'] }}</td>
                        <td>{{ ucfirst($child['gender'] ?? '-') }}</td>
                        <td>{{ $child['age'] ?? '-' }}</td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
              </table>
            @endif

            <!-- Education Section -->
            @if($employee['education'] && count($employee['education']) > 0)
              <h5 class="mt-4 mb-3">Education</h5>
              <table class="table table-bordered table-striped">
                <thead class="table-light">
                  <tr>
                    <th>Institution Name</th>
                    <th>Degree/Qualification</th>
                    <th>Year of Passing</th>
                    <th>Percentage</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee['education'] as $edu)
                    @if(!empty($edu['institution_name']))
                      <tr>
                        <td>{{ $edu['institution_name'] }}</td>
                        <td>{{ $edu['degree'] ?? '-' }}</td>
                        <td>{{ $edu['year_of_passing'] ?? '-' }}</td>
                        <td>{{ $edu['percentage'] ? $edu['percentage'] . '%' : '-' }}</td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
              </table>
            @endif

            <!-- Additional Skills Section -->
            @if($employee['additional_skills'])
              <h5 class="mt-4 mb-3">Additional Skills</h5>
              <table class="table">
                <tbody>
                  <tr>
                    <td>{{ $employee['additional_skills'] }}</td>
                  </tr>
                </tbody>
              </table>
            @endif

            <!-- Employment History Section -->
            @if($employee['employment_history'] && count($employee['employment_history']) > 0)
              <h5 class="mt-4 mb-3">Employment History</h5>
              <table class="table table-bordered table-striped">
                <thead class="table-light">
                  <tr>
                    <th>Company Name</th>
                    <th>Designation</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Salary</th>
                    <th>Reason for Leaving</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee['employment_history'] as $emp)
                    @if(!empty($emp['company_name']))
                      <tr>
                        <td>{{ $emp['company_name'] }}</td>
                        <td>{{ $emp['designation'] ?? '-' }}</td>
                        <td>{{ $emp['from_date'] ? \Carbon\Carbon::parse($emp['from_date'])->format('d-m-Y') : '-' }}</td>
                        <td>{{ $emp['to_date'] ? \Carbon\Carbon::parse($emp['to_date'])->format('d-m-Y') : '-' }}</td>
                        <td>{{ $emp['salary'] ? 'Rs. ' . number_format($emp['salary']) : '-' }}</td>
                        <td>{{ $emp['reason_for_leaving'] ?? '-' }}</td>
                      </tr>
                    @endif
                  @endforeach
                </tbody>
              </table>
            @endif
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
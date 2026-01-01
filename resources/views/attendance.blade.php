@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Attendance Table</h4>
            <div class="card-header-action">
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Return</a>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <form action="{{ route('attendance.filter') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
                  <div class="row">
                    <div class="form-group col-md-5">                    
                      <label>Employee</label>
                      @php $selectedEmployee = 'All Employees'; @endphp
                      <select class="form-control select2" name="employee_id" required>
                        <option value="0" selected>All</option>
                        @if($employee->count())
                          @foreach($employee as $item)
                            @php if($item->attendance_id == $eid){
                              $selectedEmployee = $item->employee_no . ' - ' . $item->name;
                            } @endphp
                            <option value="{{$item->attendance_id}}" {{ $eid == $item->attendance_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}}</option>
                          @endforeach
                        @endif
                      </select>
                      <div class="valid-feedback">Good job!</div>
                    </div>
                    <div class="form-group col-md-3">                    
                      <label>Date From</label>
                      <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                      <div class="valid-feedback">Good job!</div>
                    </div>
                    <div class="form-group col-md-3">                    
                      <label>Date To</label>
                      <input type="text" class="form-control datepicker" name="dto" value="{{$dto}}" required>
                      <div class="valid-feedback">Good job!</div>
                    </div>
                    <div class="form-group col-md-1 mt-4">     
                      <button class="btn btn-primary mt-2" type="submit">Filter</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                    <thead>
                      @if(!empty($dfrom) && !empty($dto))
                        <tr>
                          <th colspan="2"><b>{{ $selectedEmployee }}</b></th>
                          <th colspan="3"><b>Date From:</b> {{date("d-m-Y", strtotime($dfrom))}}</th>
                          <th colspan="3"><b>Date To:</b> {{date("d-m-Y", strtotime($dto))}}</th>
                        </tr>
                      @endif
                      <tr>
                        <th>Sr.</th>
                        @if($selectedEmployee == 'All Employees') <th>Name</th> @endif
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Late Minutes</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(isset($rows) && count($rows) > 0)
                        @php $holiday = ''; $date = ''; $index = 1; @endphp
                        @foreach($rows as $row)
                          @if($holiday != $row['HOLIDAY'] || $date != $row['DATE'] || $row['HOLIDAY'] !== '1')
                            @php $holiday = $row['HOLIDAY']; $date = $row['DATE']; @endphp
                            <tr>
                              <td>{{ $index++ }}</td>
                              {{-- <td>{{ $row['USERID'] }}</td> --}}
                              @if($selectedEmployee == 'All Employees')
                                @if($holiday == '1') <td>Holiday</td>
                                @else <td>{{ $row['ENO'] }} - {{ $row['NAME'] }}</td>@endif
                              @endif
                              <td>{{ $row['DATE'] }}</td>
                              <td>{{ $row['CHECKIN'] !== 'N/A' ? date('h:i A', strtotime($row['CHECKIN'])) : $row['CHECKIN'] }}</td>
                              <td>{{ $row['CHECKOUT'] !== 'N/A' ? date('h:i A', strtotime($row['CHECKOUT'])) : $row['CHECKOUT'] }}</td>
                              <td>{{ $row['LATE_MINUTES'] }}</td>
                              <td>
                                <span style="color: {{ $row['DETAIL'] == 'Absent' || $row['DETAIL'] == 'Single Attendance' ? '#FC544B' : '#54CA68' }}">
                                  {{ $row['DETAIL'] }}
                                </span>
                              </td>
                            </tr>
                          @endif
                        @endforeach
                      @else
                        <tr>
                          <td colspan="7">No data found</td>
                        </tr>
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        @if($selectedEmployee == 'All Employees') <th>Name</th> @endif
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Late Minutes</th>
                        <th>Status</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Attendance Summary</h4>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <form action="{{ route('attendance.summary') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
                  <div class="row">
                    <div class="form-group col-md-5">                    
                      <label>Date From</label>
                      <input type="hidden" name="employee_id" value="0" required>
                      <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                      <div class="valid-feedback">Good job!</div>
                    </div>
                    <div class="form-group col-md-5">                    
                      <label>Date To</label>
                      <input type="text" class="form-control datepicker" name="dto" value="{{$dto}}" required>
                      <div class="valid-feedback">Good job!</div>
                    </div>
                    <div class="form-group col-md-2 mt-4">     
                      <button class="btn btn-primary mt-2" type="submit">Filter</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  @if(!empty($dfrom) && !empty($dto))
                    <tr>
                      <th colspan="2"></th>
                      <th colspan="2"><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</th>
                      <th colspan="4"><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</th>
                    </tr>
                  @endif
                  <tr>
                    <th>Sr.</th>
                    <th>Employee Name</th>
                    <th>Monthly Salary</th>
                    <th>Salary Advance</th>
                    <th>Remaining</th>
                    <th>Late Time</th>
                    <th>Absents</th>
                    <th>Single Attendance</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($summary as $data)
                  @php 
                    $amount = $transaction->firstWhere('payee_id', $data['EID']);
                    $debit = $amount ? $amount->debit : 0;
                  @endphp
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $data['NAME'] }}</td>
                      <td>{{ number_format($data['SALARY']) }}</td>
                      <td>{{ number_format($debit) }}</td>
                      <td>{{ number_format($data['SALARY'] - $debit) }}</td>
                      <td>{{ $data['TOTAL_LATE'] }}</td>
                      <td>{{ $data['TOTAL_ABSENTS'] }}</td>
                      <td>{{ $data['TOTAL_SINGLE_ATTENDANCE'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee Name</th>
                    <th>Monthly Salary</th>
                    <th>Salary Advance</th>
                    <th>Remaining</th>
                    <th>Late Time</th>
                    <th>Absents</th>
                    <th>Single Attendance</th>
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
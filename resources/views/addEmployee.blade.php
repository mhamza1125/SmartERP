@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Employee</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('employee.store') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Department</label>
                    <select class="form-control select2" name="department_id" required>
                      <option value="" selected disabled>Select Department</option>
                      @if($department->count())
                        @foreach($department as $item)
                          <option value="{{$item->head_id}}" {{ old('department_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Department</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>City</label>
                    <select class="form-control select2" name="city_id" required>
                      <option value="" selected disabled>Select City</option>
                      @if($city->count())
                        @foreach($city as $item)
                          <option value="{{$item->head_id}}" {{ old('city_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select City</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee Type</label>
                    <select class="form-control" name="employee_type_id" required>
                      <option value="" selected disabled>Select Employee Type</option>
                      @if($employeeType->count())
                        @foreach($employeeType as $item)
                          <option value="{{$item->head_id}}" {{ old('employee_type_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee Type</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee No</label>
                    <input type="text" class="form-control" name="employee_no" required value="{{$count}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Joining Date</label>
                    <input type="text" class="form-control datepicker" name="joining_date" required value="{{old('joining_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee Status</label>
                    <select class="form-control" name="employee_status" required>
                      <option value="1" {{ old('employee_status') == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ old('employee_status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" required value="{{old('name')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Short Name</label>
                    <input type="text" class="form-control" name="sname" value="{{old('sname')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Father Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{old('fname')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Father Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>CNIC</label>
                    <input type="text" class="form-control" name="cnic" required value="{{old('cnic')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee CNIC</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone1" required value="{{old('phone1')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Contact No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Emergency No</label>
                    <input type="text" class="form-control" name="phone2" value="{{old('phone2')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee Salary (Optional)</label>
                    <input type="number" min="0" class="form-control" name="salary" value="{{old('salary')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Salary</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>File / Images</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="customFile" name="image[]" multiple>
                      <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                    <div class="valid-feedback" id="fileSuccess">Good job!</div>
                    <div class="invalid-feedback" id="fileError"></div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" name="address" required>{{old('address')}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Address</div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{old('description')}}</textarea>
                  </div>
                </div>
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
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
@endsection

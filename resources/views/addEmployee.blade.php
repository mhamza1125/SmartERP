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
                    <label>Designation</label>
                    <input type="text" class="form-control" name="designation" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Designaiton</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>City</label>
                    <select class="form-control select2" id="city_id" name="city_id" required>
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
                <div class="col-md-1">
                  <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#createCityModal">
                      <i class="fas fa-plus"></i>
                    </button>
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
                    <input type="number" min="0" class="form-control" name="salary" value="0">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Salary</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Opening Balance</label>
                    <input type="number" class="form-control" name="credit" required value="{{old('credit') ?? '0'}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Balance</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Payable / Receiveable</label>
                    <select class="form-control" name="balance_type" required>
                      <option value="credit" {{ old('balance_type') == 'credit' ? 'selected' : '' }}>Receiveable</option>
                      <option value="debit" {{ old('balance_type') == 'debit' ? 'selected' : '' }}>Payable</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Attendance No</label>
                    <input type="text" class="form-control" name="attendance_id" value="{{ old('attendance_id') }}">
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
                    <textarea class="form-control" name="description">{{old('description')}}</textarea>
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

<!-- Create City Modal -->
<div class="modal fade" id="createCityModal" tabindex="-1" role="dialog" aria-labelledby="createCityModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createCityModalLabel">Create New City</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="createCityForm">
          @csrf
          <div class="form-group">
            <label for="city_name">City Name</label>
            <input type="text" class="form-control" id="city_name" name="name" required>
            <div class="invalid-feedback" id="city_name_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="createCity()">Create City</button>
      </div>
    </div>
  </div>
</div>

<script>
function createCity() {
    var cityName = $('#city_name').val();

    if (!cityName) {
        $('#city_name').addClass('is-invalid');
        $('#city_name_error').text('City name is required');
        return;
    }

    $.ajax({
        url: '{{ route("head.store") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            name: cityName,
            head_type_id: 8 // City type
        },
        success: function(response) {
            if (response.success) {
                // Add new option to select
                var newOption = new Option(cityName, response.head_id, true, true);
                $('#city_id').append(newOption).trigger('change');

                // Close modal and reset form
                $('#createCityModal').modal('hide');
                $('#createCityForm')[0].reset();
                $('#city_name').removeClass('is-invalid');

                // Show success message
                alert('City created successfully!');
            } else {
                alert('Error creating city: ' + response.message);
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            if (errors && errors.name) {
                $('#city_name').addClass('is-invalid');
                $('#city_name_error').text(errors.name[0]);
            } else {
                alert('Error creating city. Please try again.');
            }
        }
    });
}

// Reset form when modal is closed
$('#createCityModal').on('hidden.bs.modal', function () {
    $('#createCityForm')[0].reset();
    $('#city_name').removeClass('is-invalid');
    $('#city_name_error').text('');
});
</script>
@endsection

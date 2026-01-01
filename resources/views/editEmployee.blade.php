@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Employee</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('employee.update', $employee['employee_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Department</label>
                    <select class="form-control select2" name="department_id" required>
                      <option value="" selected disabled>Select Department</option>
                      @if($department->count())
                        @foreach($department as $item)
                          <option value="{{$item->head_id}}" {{ $employee['department_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                    <input type="text" class="form-control" name="designation" value="{{$employee['designation']}}">
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
                          <option value="{{$item->head_id}}" {{ $employee['city_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                    <input type="text" class="form-control" name="employee_no" required value="{{$employee['employee_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Joining Date</label>
                    <input type="text" class="form-control datepicker" name="joining_date" required value="{{$employee['joining_date']}}">
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
                          <option value="{{$item->head_id}}" {{ $employee['employee_type_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
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
                    <input type="text" class="form-control" name="name" required value="{{$employee['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Short Name</label>
                    <input type="text" class="form-control" name="sname" value="{{$employee['sname']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Father Name</label>
                    <input type="text" class="form-control" name="fname" required value="{{$employee['fname']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Father Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>CNIC</label>
                    <input type="text" class="form-control" name="cnic" required value="{{$employee['cnic']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee CNIC</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" name="phone1" required value="{{$employee['phone1']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Contact No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Emergency No</label>
                    <input type="text" class="form-control" name="phone2" value="{{$employee['phone2']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee Salary (Optional)</label>
                    <input type="number" min="0" class="form-control" name="salary" value="{{$employee['salary']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Salary</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Opening Balance</label>
                    @php
                      $obAmount = 0;
                      $obType = 'credit';
                      if ($openingBalance) {
                        $obAmount = $openingBalance->debit ?? $openingBalance->credit ?? 0;
                        $obType = $openingBalance->debit ? 'debit' : 'credit';
                      }
                    @endphp
                    <input type="number" class="form-control" name="credit" required value="{{old('credit') ?? $obAmount}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Opening Balance</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Payable / Receiveable</label>
                    <select class="form-control" name="balance_type" required>
                      <option value="credit" {{ (old('balance_type') ?? $obType) == 'credit' ? 'selected' : '' }}>Receiveable</option>
                      <option value="debit" {{ (old('balance_type') ?? $obType) == 'debit' ? 'selected' : '' }}>Payable</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Attendance No</label>
                    <input type="text" class="form-control" name="attendance_id" value="{{$employee['attendance_id']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee Status</label>
                    <select class="form-control" name="employee_status" required>
                      <option value="1" {{ $employee['employee_status'] == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ $employee['employee_status'] == '0' ? 'selected' : '' }}>Inactive</option>
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
                    <textarea class="form-control" name="address" required>{{$employee['address']}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Employee Address</div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description">{{$employee['description']}}</textarea>
                  </div>
                </div>
              </div>

              <!-- Personal Information Section -->
              <hr class="my-4">
              <h5 class="mb-3">Personal Information</h5>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Marital Status</label>
                    <select class="form-control" name="marital_status">
                      <option value="">Select Marital Status</option>
                      <option value="single" {{ $employee['marital_status'] == 'single' ? 'selected' : '' }}>Single</option>
                      <option value="married" {{ $employee['marital_status'] == 'married' ? 'selected' : '' }}>Married</option>
                      <option value="divorced" {{ $employee['marital_status'] == 'divorced' ? 'selected' : '' }}>Divorced</option>
                      <option value="widower" {{ $employee['marital_status'] == 'widower' ? 'selected' : '' }}>Widower</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Number of Siblings</label>
                    <input type="number" min="0" class="form-control" name="siblings_count" value="{{ $employee['siblings_count'] ?? '' }}">
                  </div>
                </div>
              </div>

              <!-- Children Details Section -->
              <div class="row">
                <div class="col-md-12">
                  <h6 class="mb-3">Children Details</h6>
                  <div id="children-container">
                    @php
                      $children = $employee['children_details'] ?? [];
                      if (empty($children)) {
                        $children = [['name' => '', 'gender' => '', 'age' => '']];
                      }
                    @endphp
                    @foreach($children as $index => $child)
                      <div class="children-entry card p-3 mb-2">
                        <div class="row">
                          <div class="col-md-4">
                            <input type="text" class="form-control" name="children_details[{{ $index }}][name]" placeholder="Child Name" value="{{ $child['name'] ?? '' }}">
                          </div>
                          <div class="col-md-3">
                            <select class="form-control" name="children_details[{{ $index }}][gender]">
                              <option value="">Select Gender</option>
                              <option value="male" {{ ($child['gender'] ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                              <option value="female" {{ ($child['gender'] ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <input type="number" min="0" max="100" class="form-control" name="children_details[{{ $index }}][age]" placeholder="Age" value="{{ $child['age'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-child" {{ count($children) == 1 ? 'style=display:none;' : '' }}>Remove</button>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                  <button type="button" class="btn btn-sm btn-success" id="add-child">+ Add Child</button>
                </div>
              </div>

              <!-- Education Section -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <h6 class="mb-3">Education</h6>
                  <div id="education-container">
                    @php
                      $education = $employee['education'] ?? [];
                      if (empty($education)) {
                        $education = [['institution_name' => '', 'degree' => '', 'year_of_passing' => '', 'percentage' => '']];
                      }
                    @endphp
                    @foreach($education as $index => $edu)
                      <div class="education-entry card p-3 mb-2">
                        <div class="row">
                          <div class="col-md-3">
                            <input type="text" class="form-control" name="education[{{ $index }}][institution_name]" placeholder="Institution Name" value="{{ $edu['institution_name'] ?? '' }}">
                          </div>
                          <div class="col-md-3">
                            <input type="text" class="form-control" name="education[{{ $index }}][degree]" placeholder="Degree/Qualification" value="{{ $edu['degree'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <input type="number" min="1900" max="2100" class="form-control" name="education[{{ $index }}][year_of_passing]" placeholder="Year" value="{{ $edu['year_of_passing'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <input type="number" min="0" max="100" step="0.01" class="form-control" name="education[{{ $index }}][percentage]" placeholder="Percentage" value="{{ $edu['percentage'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-education" {{ count($education) == 1 ? 'style=display:none;' : '' }}>Remove</button>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                  <button type="button" class="btn btn-sm btn-success" id="add-education">+ Add Education</button>
                </div>
              </div>

              <!-- Additional Skills Section -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Additional Skills</label>
                    <textarea class="form-control" name="additional_skills" placeholder="Enter skills separated by comma or describe in detail">{{ $employee['additional_skills'] ?? '' }}</textarea>
                  </div>
                </div>
              </div>

              <!-- Employment History Section -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <h6 class="mb-3">Employment History</h6>
                  <div id="employment-container">
                    @php
                      $employment = $employee['employment_history'] ?? [];
                      if (empty($employment)) {
                        $employment = [['company_name' => '', 'designation' => '', 'from_date' => '', 'to_date' => '', 'salary' => '', 'reason_for_leaving' => '']];
                      }
                    @endphp
                    @foreach($employment as $index => $emp)
                      <div class="employment-entry card p-3 mb-2">
                        <div class="row">
                          <div class="col-md-3">
                            <input type="text" class="form-control" name="employment_history[{{ $index }}][company_name]" placeholder="Company Name" value="{{ $emp['company_name'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <input type="text" class="form-control" name="employment_history[{{ $index }}][designation]" placeholder="Designation" value="{{ $emp['designation'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <input type="date" class="form-control" name="employment_history[{{ $index }}][from_date]" placeholder="From Date" value="{{ $emp['from_date'] ?? '' }}">
                          </div>
                          <div class="col-md-2">
                            <input type="date" class="form-control" name="employment_history[{{ $index }}][to_date]" placeholder="To Date" value="{{ $emp['to_date'] ?? '' }}">
                          </div>
                          <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm remove-employment" {{ count($employment) == 1 ? 'style=display:none;' : '' }}>Remove</button>
                          </div>
                        </div>
                        <div class="row mt-2">
                          <div class="col-md-4">
                            <input type="number" min="0" step="0.01" class="form-control" name="employment_history[{{ $index }}][salary]" placeholder="Salary" value="{{ $emp['salary'] ?? '' }}">
                          </div>
                          <div class="col-md-8">
                            <input type="text" class="form-control" name="employment_history[{{ $index }}][reason_for_leaving]" placeholder="Reason for Leaving" value="{{ $emp['reason_for_leaving'] ?? '' }}">
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                  <button type="button" class="btn btn-sm btn-success" id="add-employment">+ Add Employment</button>
                </div>
              </div>

              <div class="form-group row mb-4 mt-4">
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

// Dynamic Children Details
let childCount = {{ count($employee['children_details'] ?? []) }};
$('#add-child').click(function() {
    const childHtml = `
        <div class="children-entry card p-3 mb-2">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="children_details[${childCount}][name]" placeholder="Child Name">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="children_details[${childCount}][gender]">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" min="0" max="100" class="form-control" name="children_details[${childCount}][age]" placeholder="Age">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-child">Remove</button>
                </div>
            </div>
        </div>
    `;
    $('#children-container').append(childHtml);
    childCount++;
    updateRemoveButtons();
});

// Dynamic Education
let eduCount = {{ count($employee['education'] ?? []) }};
$('#add-education').click(function() {
    const eduHtml = `
        <div class="education-entry card p-3 mb-2">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="education[${eduCount}][institution_name]" placeholder="Institution Name">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="education[${eduCount}][degree]" placeholder="Degree/Qualification">
                </div>
                <div class="col-md-2">
                    <input type="number" min="1900" max="2100" class="form-control" name="education[${eduCount}][year_of_passing]" placeholder="Year">
                </div>
                <div class="col-md-2">
                    <input type="number" min="0" max="100" step="0.01" class="form-control" name="education[${eduCount}][percentage]" placeholder="Percentage">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-education">Remove</button>
                </div>
            </div>
        </div>
    `;
    $('#education-container').append(eduHtml);
    eduCount++;
    updateRemoveButtons();
});

// Dynamic Employment History
let empCount = {{ count($employee['employment_history'] ?? []) }};
$('#add-employment').click(function() {
    const empHtml = `
        <div class="employment-entry card p-3 mb-2">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="employment_history[${empCount}][company_name]" placeholder="Company Name">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" name="employment_history[${empCount}][designation]" placeholder="Designation">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="employment_history[${empCount}][from_date]" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="employment_history[${empCount}][to_date]" placeholder="To Date">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-employment">Remove</button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <input type="number" min="0" step="0.01" class="form-control" name="employment_history[${empCount}][salary]" placeholder="Salary">
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="employment_history[${empCount}][reason_for_leaving]" placeholder="Reason for Leaving">
                </div>
            </div>
        </div>
    `;
    $('#employment-container').append(empHtml);
    empCount++;
    updateRemoveButtons();
});

// Remove buttons functionality
$(document).on('click', '.remove-child', function(e) {
    e.preventDefault();
    $(this).closest('.children-entry').remove();
    updateRemoveButtons();
});

$(document).on('click', '.remove-education', function(e) {
    e.preventDefault();
    $(this).closest('.education-entry').remove();
    updateRemoveButtons();
});

$(document).on('click', '.remove-employment', function(e) {
    e.preventDefault();
    $(this).closest('.employment-entry').remove();
    updateRemoveButtons();
});

// Update remove button visibility
function updateRemoveButtons() {
    $('.remove-child').show();
    $('.remove-education').show();
    $('.remove-employment').show();

    if ($('.children-entry').length === 1) {
        $('.children-entry .remove-child').hide();
    }
    if ($('.education-entry').length === 1) {
        $('.education-entry .remove-education').hide();
    }
    if ($('.employment-entry').length === 1) {
        $('.employment-entry .remove-employment').hide();
    }
}

// Initialize on page load
$(document).ready(function() {
    updateRemoveButtons();
});
</script>
@endsection
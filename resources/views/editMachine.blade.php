@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Machine</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('machine.update', $machine['machine_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Machine No</label>
                    <input type="text" class="form-control" name="machine_no" required value="{{$machine['machine_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Machine No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Machine Type</label>
                    <select class="form-control select2" name="machine_type_id" required>
                      <option value="" selected disabled>Select Machine Type</option>
                      @if($head->count())
                        @foreach($head as $item)
                          <option value="{{$item->head_id}}" {{ $machine['machine_type_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Machine Type</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">          
                    <label>Current Employee</label>
                    <select class="form-control select2" name="employee_id" required>
                      <option value="" selected disabled>Select Employee</option>
                      <option value="0" {{ $machine['employee_id'] == '0' ? 'selected' : '' }}>Not Asigned</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option value="{{$item->employee_id}}" {{ $machine['employee_id'] == $item->employee_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Machine Location</label>
                    <input type="text" class="form-control" name="location" value="{{$machine['location']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Machine Location</div>
                  </div>
                </div>
                <div class="col-md-6">
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
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$machine['description']}}</textarea>
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
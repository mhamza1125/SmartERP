@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Box</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('box.update', $box['box_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Box No</label>
                    <input type="text" class="form-control" name="box_no" required value="{{$box['box_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Box No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Box Name</label>
                    <input type="text" class="form-control" name="name" required value="{{$box['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Box Name</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">          
                    <label>Current Vendor</label>
                    <select class="form-control select2" name="vendor_id" required id="vendor_id">
                      <option value="" selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ $box['vendor_id'] == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Box Weight (Grams)</label>
                    <input type="number" class="form-control" min="0" step="0.01" name="weight" required value="{{$box['weight']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Box Weight</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Box Material</label>
                    <select class="form-control select2" name="head_id" required>
                      <option value="" selected disabled>Select Box Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->head_id}}" {{ $box['head_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Box Material</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Box Status</label>
                    <select class="form-control" name="box_status" required>
                      <option value="1" {{ $box['box_status'] == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ $box['box_status'] == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Dimensions (Length - Width - Height) Inches</label>
                    <div class="row">
                      <div class="col">
                        <input type="number" class="form-control" min="0" step="0.01" name="length" placeholder="Length" required value="{{$box['length']}}">
                        <div class="valid-feedback">Good job!</div>
                        <div class="invalid-feedback">Enter Length</div>
                      </div>
                      <div class="col">
                        <input type="number" class="form-control" min="0" step="0.01" name="width" placeholder="Width" required value="{{$box['width']}}">
                        <div class="valid-feedback">Good job!</div>
                        <div class="invalid-feedback">Enter Width</div>
                      </div>
                      <div class="col">
                        <input type="number" class="form-control" min="0" step="0.01" name="height" placeholder="Height" required value="{{$box['height']}}">
                        <div class="valid-feedback">Good job!</div>
                        <div class="invalid-feedback">Enter Height</div>
                      </div>
                    </div>
                </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Box Images</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="customFile" name="image[]" multiple>
                      <label class="custom-file-label" for="customFile">Choose Images</label>
                    </div>
                    <div class="valid-feedback" id="fileSuccess">Good job!</div>
                    <div class="invalid-feedback" id="fileError"></div>
                  </div>
                </div>
              </div>
              <div id="attachmentContainer">
                <div class="row attachment-row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Attachment Title</label>
                      <input type="text" class="form-control attachment-title" name="file_title[]" value="">
                      <div class="valid-feedback">Good job!</div>
                      <div class="invalid-feedback">Enter Attachment Title</div>
                    </div>
                  </div>
                  <div class="col-md-5">
                    <div class="form-group">
                        <label>Attach Files</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input attachment-file" id="customFile2" name="file[]">
                            <label class="custom-file-label" for="customFile2">Choose file</label>
                        </div>
                        <div class="valid-feedback attachment-success">Good job!</div>
                        <div class="invalid-feedback attachment-error"></div>
                    </div>
                  </div>                
                  <div class="col-md-1">
                    <div class="form-group">
                      <label class="add-attachment-label">&nbsp</label>
                      <label class="remove-attachment-label" style="display:none;">&nbsp</label>
                      <div>
                        <button type="button" class="btn btn-primary add-attachment">+</button>
                      <button type="button" class="btn btn-danger remove-attachment" style="display:none;">X</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>   
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$box['description']}}</textarea>
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Product</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('product.update', $product['product_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Category</label>
                    <select class="form-control select2" name="category_id" required>
                      <option value="" selected disabled>Select Category</option>
                      @if($category->count())
                        @foreach($category as $item)
                          <option value="{{$item->category_id}}" {{ $product['category_id'] == $item->category_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Category</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Unit</label>
                    <select class="form-control select2" name="unit_id" required>
                      <option value="" selected disabled>Select Unit</option>
                      @if($unit->count())
                        @foreach($unit as $item)
                          <option value="{{$item->head_id}}" {{ $product['unit_id'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Unit</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Product Status</label>
                    <select class="form-control" name="product_status" required>
                      <option value="1" {{ $product['product_status'] == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ $product['product_status'] == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Aricle No</label>
                    <input type="text" class="form-control" name="article_no" required value="{{$product['article_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Article No</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" class="form-control" name="name" required value="{{$product['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Product Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Sizes</label>
                    <select class="form-control select2" name="size_id[]" multiple="" id="size_id" required>
                      <option value="" disabled>Select Sizes</option>
                      @if($size->count())
                        @foreach($size as $item)
                            <option value="{{ $item->head_id }}" {{ $productType->pluck('head_id')->contains($item->head_id) ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Size</div>
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#createSizeModal">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Images</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="customFile" name="image[]" multiple>
                      <label class="custom-file-label" for="customFile">Choose Images</label>
                    </div>
                    <div class="valid-feedback" id="fileSuccess">Good job!</div>
                    <div class="invalid-feedback" id="fileError"></div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Product Stages</label>
                    <select class="form-control select2" name="stage_ids[]" multiple="" required>
                      <option value="" disabled>Select Stages</option>
                      @if($stage->count())
                        @foreach($stage as $item)
                          @unless($item->material_type_id == '61' || $item->material_type_id == '96')
                            <option value="{{$item->head_id}}" {{ $pstage->pluck('head_id')->contains($item->head_id) ? 'selected' : '' }}>{{$item->name}}</option>
                          @endunless
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Product Materials</label>
                    <select class="form-control select2" name="material_id[]" multiple="" required>
                      <option value="" disabled>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" {{ $pmaterial->pluck('material_id')->contains($item->material_id) ? 'selected' : '' }}>{{$item->material_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
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
                    <textarea class="summernote" name="description">{{$product['description']}}</textarea>
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

<!-- Create Size Modal -->
<div class="modal fade" id="createSizeModal" tabindex="-1" role="dialog" aria-labelledby="createSizeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createSizeModalLabel">Create New Size</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="createSizeForm">
          @csrf
          <div class="form-group">
            <label for="size_name">Size Name</label>
            <input type="text" class="form-control" id="size_name" name="name" required>
            <div class="invalid-feedback" id="size_name_error"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="createSize()">Create Size</button>
      </div>
    </div>
  </div>
</div>

<script>
function createSize() {
    var sizeName = $('#size_name').val();

    if (!sizeName) {
        $('#size_name').addClass('is-invalid');
        $('#size_name_error').text('Size name is required');
        return;
    }

    $.ajax({
        url: '{{ route("head.store") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            name: sizeName,
            head_type_id: 1 // Size type
        },
        success: function(response) {
            if (response.success) {
                // Add new option to select
                var newOption = new Option(sizeName, response.head_id, true, true);
                $('#size_id').append(newOption);

                // Close modal and reset form
                $('#createSizeModal').modal('hide');
                $('#createSizeForm')[0].reset();
                $('#size_name').removeClass('is-invalid');

                // Show success message
                alert('Size created successfully!');
            } else {
                alert('Error creating size: ' + response.message);
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            if (errors && errors.name) {
                $('#size_name').addClass('is-invalid');
                $('#size_name_error').text(errors.name[0]);
            } else {
                alert('Error creating size. Please try again.');
            }
        }
    });
}

// Reset form when modal is closed
$('#createSizeModal').on('hidden.bs.modal', function () {
    $('#createSizeForm')[0].reset();
    $('#size_name').removeClass('is-invalid');
    $('#size_name_error').text('');
});
</script>

@endsection
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
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Article No</label>
                    <input type="text" class="form-control" name="article_no" required value="{{$product['article_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Article No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>HS Code</label>
                    <input type="text" class="form-control" name="hs_code" value="{{$product['hs_code'] ?? ''}}" placeholder="Enter HS Code">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter HS Code</div>
                  </div>
                </div>
                <div class="col-md-4">
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
                    <select class="form-control select2" name="stage_ids[]" multiple="" required id="stage_ids_select">
                      <option value="" disabled>Select Stages</option>
                      {{-- First display selected stages in their original order --}}
                      @if($pstage->count())
                        @foreach($pstage as $selectedStage)
                          <option value="{{$selectedStage->head_id}}" selected>{{$selectedStage->name}}</option>
                        @endforeach
                      @endif
                      {{-- Then display remaining unselected stages --}}
                      @if($stage->count())
                        @foreach($stage as $item)
                          @unless($item->material_type_id == '61' || $item->material_type_id == '96')
                            @unless($pstage->pluck('head_id')->contains($item->head_id))
                              <option value="{{$item->head_id}}">{{$item->name}}</option>
                            @endunless
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
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Product Components (Other Products Used in Manufacturing)</label>
                    <div id="product-components-container">
                      @if(isset($existingProductComponents) && count($existingProductComponents) > 0)
                        @foreach($existingProductComponents as $component)
                          <div class="row product-component-row existing-component-row mb-2">
                            <div class="col-md-6">
                              <input type="text" class="form-control" value="{{ $component->article_no }} - {{ $component->product_name }} ({{ $component->size_name }})" disabled>
                              <input type="hidden" name="component_product_type_id[]" value="{{ $component->component_pt_id }}">
                            </div>
                            <div class="col-md-3">
                              <input type="number" step="0.001" class="form-control" name="component_quantity[]" value="{{$component->quantity}}" placeholder="Quantity" min="0.001" required>
                            </div>
                            <div class="col-md-3">
                              <button type="button" class="btn btn-sm btn-danger remove-product-component">
                                <i class="fas fa-trash"></i> Remove
                              </button>
                            </div>
                          </div>
                        @endforeach
                      @else
                        <div class="alert alert-info" id="no-components-message">
                          <i class="fas fa-info-circle"></i> Add other finished products that are used as components in manufacturing this product.
                        </div>
                      @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="add-product-component">
                      <i class="fas fa-plus"></i> Add Product Component
                    </button>
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
                    <label>Opening Stock by Size and Stage</label>
                    <div id="opening-stock-container">
                      @if($openingStock->count() > 0)
                        @foreach($openingStock as $stock)
                          <div class="row opening-stock-row mb-2">
                            <div class="col-md-3">
                              <select class="form-control" name="opening_stock_size_id[]" required>
                                <option value="">Select Size</option>
                                @foreach($size as $sizeItem)
                                  <option value="{{$sizeItem->head_id}}" {{$stock->size_id == $sizeItem->head_id ? 'selected' : ''}}>{{$sizeItem->name}}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-md-3">
                              <select class="form-control" name="opening_stock_stage_id[]" required>
                                <option value="">Select Stage</option>
                                @foreach($pstage as $stage)
                                  <option value="{{$stage->head_id}}" {{$stock->stage_id == $stage->head_id ? 'selected' : ''}}>{{$stage->name}}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-md-3">
                              <input type="number" step="0.001" class="form-control" name="opening_stock_quantity[]" value="{{$stock->quantity}}" placeholder="Quantity" min="0" required>
                            </div>
                            <div class="col-md-3">
                              <button type="button" class="btn btn-sm btn-danger remove-opening-stock">
                                <i class="fas fa-trash"></i> Remove
                              </button>
                            </div>
                          </div>
                        @endforeach
                      @else
                        <div class="alert alert-info">
                          <i class="fas fa-info-circle"></i> Select product sizes and stages first, then add opening stock for each size-stage combination below.
                        </div>
                      @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="add-opening-stock">
                      <i class="fas fa-plus"></i> Add Opening Stock
                    </button>
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

// Preserve selection order for stage multiselect
$(document).ready(function() {
    var stageSelect = $('#stage_ids_select');
    var selectedOrder = [];

    // Initialize with current selection order
    stageSelect.find('option:selected').each(function() {
        selectedOrder.push($(this).val());
    });

    // Handle selection changes
    stageSelect.on('select2:select', function(e) {
        var selectedValue = e.params.data.id;
        if (selectedOrder.indexOf(selectedValue) === -1) {
            selectedOrder.push(selectedValue);
        }
        updateSelectionOrder();
    });

    stageSelect.on('select2:unselect', function(e) {
        var unselectedValue = e.params.data.id;
        var index = selectedOrder.indexOf(unselectedValue);
        if (index > -1) {
            selectedOrder.splice(index, 1);
        }
        updateSelectionOrder();
    });

    function updateSelectionOrder() {
        // Reorder options to match selection order
        var selectElement = stageSelect[0];
        var selectedOptions = [];
        var unselectedOptions = [];

        // Separate selected and unselected options
        $(selectElement).find('option').each(function() {
            if ($(this).prop('selected')) {
                selectedOptions.push(this);
            } else if ($(this).val() !== '') { // Skip disabled option
                unselectedOptions.push(this);
            }
        });

        // Clear all options except the disabled one
        $(selectElement).find('option:not([disabled])').remove();

        // Add selected options in the order they were selected
        selectedOrder.forEach(function(value) {
            var option = selectedOptions.find(function(opt) {
                return opt.value === value;
            });
            if (option) {
                $(selectElement).append(option);
            }
        });

        // Add unselected options
        unselectedOptions.forEach(function(option) {
            $(selectElement).append(option);
        });

        // Trigger change to update Select2
        stageSelect.trigger('change.select2');
    }
});

// Opening Stock Management
$(document).ready(function() {
    var openingStockCounter = $('.opening-stock-row').length;

    // Add opening stock row
    $('#add-opening-stock').click(function() {
        var selectedSizes = $('#size_id').val();
        var selectedStages = $('#stage_ids_select').val();

        if (!selectedSizes || selectedSizes.length === 0) {
            alert('Please select product sizes first.');
            return;
        }

        if (!selectedStages || selectedStages.length === 0) {
            alert('Please select product stages first.');
            return;
        }

        var sizeOptions = '';
        $('#size_id option:selected').each(function() {
            sizeOptions += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
        });

        var stageOptions = '';
        $('#stage_ids_select option:selected').each(function() {
            stageOptions += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
        });

        var row = `
            <div class="row opening-stock-row mb-2" data-index="${openingStockCounter}">
                <div class="col-md-3">
                    <select class="form-control" name="opening_stock_size_id[]" required>
                        <option value="">Select Size</option>
                        ${sizeOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="opening_stock_stage_id[]" required>
                        <option value="">Select Stage</option>
                        ${stageOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.001" class="form-control" name="opening_stock_quantity[]" placeholder="Quantity" min="0" required>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-danger remove-opening-stock">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;

        $('#opening-stock-container').append(row);
        openingStockCounter++;
    });

    // Remove opening stock row
    $(document).on('click', '.remove-opening-stock', function() {
        $(this).closest('.opening-stock-row').remove();
    });

    // Update available sizes and stages when selections change
    function updateOpeningStockOptions() {
        var selectedSizes = $('#size_id').val() || [];
        var selectedStages = $('#stage_ids_select').val() || [];

        var sizeOptions = '<option value="">Select Size</option>';
        $('#size_id option:selected').each(function() {
            sizeOptions += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
        });

        var stageOptions = '<option value="">Select Stage</option>';
        $('#stage_ids_select option:selected').each(function() {
            stageOptions += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
        });

        $('.opening-stock-row select[name="opening_stock_size_id[]"]').each(function() {
            var currentValue = $(this).val();
            $(this).html(sizeOptions);
            if (selectedSizes.includes(currentValue)) {
                $(this).val(currentValue);
            }
        });

        $('.opening-stock-row select[name="opening_stock_stage_id[]"]').each(function() {
            var currentValue = $(this).val();
            $(this).html(stageOptions);
            if (selectedStages.includes(currentValue)) {
                $(this).val(currentValue);
            }
        });
    }

    // Update opening stock options when size or stage selection changes
    $('#size_id, #stage_ids_select').on('change', function() {
        updateOpeningStockOptions();
    });
});

// Product Components Management
$(document).ready(function() {
    var productComponentCounter = $('.product-component-row').length;
    var productTypes = @json($productTypes ?? []);

    // Build product options HTML
    function getProductOptions() {
        var options = '<option value="">Select Product</option>';
        productTypes.forEach(function(product) {
            options += '<option value="' + product.product_type_id + '">' +
                product.article_no + ' - ' + product.name + ' (' + product.hname + ')</option>';
        });
        return options;
    }

    // Add product component row (new components use Select2)
    $('#add-product-component').click(function() {
        // Hide the "no components" message if it exists
        $('#no-components-message').hide();

        var productOptions = getProductOptions();

        var row = `
            <div class="row product-component-row new-component-row mb-2" data-index="${productComponentCounter}">
                <div class="col-md-6">
                    <select class="form-control select2-product-component" name="component_product_type_id[]" required>
                        ${productOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.001" class="form-control" name="component_quantity[]" placeholder="Quantity" min="0.001" value="1" required>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-danger remove-product-component">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;

        $('#product-components-container').append(row);

        // Initialize Select2 for the new dropdown
        $('.select2-product-component').last().select2({
            placeholder: 'Select Product',
            allowClear: true,
            width: '100%'
        });

        productComponentCounter++;
    });

    // Remove product component row
    $(document).on('click', '.remove-product-component', function() {
        $(this).closest('.product-component-row').remove();
    });
});
</script>

@endsection
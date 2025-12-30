@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Create Process Travel Card (PTC)</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc') }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <input type="hidden" name="order_id" value="{{ $order_id }}">
              <input type="hidden" name="ptc_product_type_id" value="{{ $product_type_id }}">
              <input type="hidden" name="start_stage_id" value="{{ $start_stage_id }}">
              <input type="hidden" name="end_stage_id" value="{{ $end_stage_id }}">
              
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>PTC No</label>
                    <input type="text" class="form-control" value="PTC-{{ $ptcNo }}" readonly>
                    <input type="hidden" name="stock_no" value="{{ $ptcNo }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order</label>
                    <input type="text" class="form-control" value="{{ $order ? $order->job_no : 'Default PTC' }}" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Product</label>
                    <input type="text" class="form-control" value="{{ $productType ? $productType->name . ' - ' . $productType->size_name : 'N/A' }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>PTC Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{ old('stock_date', date('Y-m-d')) }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Start Stage</label>
                    <input type="text" class="form-control" value="{{ $startStage->name }}" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>End Stage</label>
                    <input type="text" class="form-control" value="{{ $endStage->name }}" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Order Qty</label>
                    <input type="text" class="form-control bg-light" value="{{ $orderQuantity ?? 'N/A' }}" readonly>
                    <small class="text-muted">From order</small>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Already Issued</label>
                    <input type="text" class="form-control bg-light" value="{{ $alreadyIssuedQty ?? 0 }}" readonly>
                    <small class="text-muted">Previous PTCs</small>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>PTC Qty <span class="text-danger">*</span></label>
                    <input type="number" min="1" class="form-control" name="product_quantity" required value="{{ old('product_quantity', $orderQuantity ?? 1) }}">
                  </div>
                </div>
              </div>

              <!-- Stage Progress Display -->
              <div class="row mb-3">
                <div class="col-md-12">
                  <label>PTC Stages</label>
                  <div class="d-flex flex-wrap">
                    @foreach($stages as $index => $stage)
                      <div class="badge {{ $index == 0 ? 'badge-primary' : 'badge-secondary' }} mr-2 mb-2 p-2">
                        {{ $index + 1 }}. {{ $stage->name }}
                        @if($index == 0) <small>(Start)</small> @endif
                        @if($index == count($stages) - 1) <small>(End)</small> @endif
                      </div>
                      @if($index < count($stages) - 1)
                        <div class="mr-2 mb-2 p-2"><i class="fas fa-arrow-right"></i></div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>

              <hr>
              <h5>Issue Items for First Stage: {{ $startStage->name }}</h5>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Employee / Vendor <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="employee_id" id="employee_id" required>
                      <option value="" selected disabled>Select Employee / Vendor</option>
                      @if($employees->count())
                        @foreach($employees as $item)
                          <option data-type="employee" value="{{ $item->employee_id }}">{{ $item->employee_no }} - {{ $item->name }}</option>
                        @endforeach
                      @endif
                      @if($vendors->count())
                        @foreach($vendors as $item)
                          <option data-type="vendor" value="{{ $item->vendor_id }}">{{ $item->vendor_no }} - {{ $item->fname }}</option>
                        @endforeach
                      @endif
                    </select>
                    <input type="hidden" id="table_name" name="table_name">
                  </div>
                </div>
              </div>

              <!-- Material Issuance Section -->
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id" id="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @foreach($materials as $material)
                        <option value="{{ $material->material_id }}">{{ $material->name }} | {{ $material->uname ?? 'Unit' }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Available Stock</label>
                    <input type="text" class="form-control" id="available_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="quantityMaterial" id="quantityMaterial" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnMaterial" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              {{-- Material Quantity Tracking --}}
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Required &nbsp|&nbsp Issued &nbsp|&nbsp To Issue (Complete Order)</label>
                    <input type="text" class="form-control" id="materialQty" readonly placeholder="0  |  0  |  0">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Required &nbsp|&nbsp Issued &nbsp|&nbsp To Issue (Selected Article)</label>
                    <input type="text" class="form-control" id="articleQty" readonly placeholder="0  |  0  |  0">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Required &nbsp|&nbsp Issued &nbsp|&nbsp To Issue (Selected Article Size)</label>
                    <input type="text" class="form-control" id="articleTQty" readonly placeholder="0  |  0  |  0">
                  </div>
                </div>
              </div>

              {{-- Product Components Section --}}
              @if(isset($productComponents) && $productComponents->count() > 0)
              <div class="row" id="product-components-row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product Components <small class="text-muted">(Other products used in manufacturing)</small></label>
                    <select class="form-control select2" name="component_id" id="component_id">
                      <option value="" disabled selected>Select Product Component</option>
                      @foreach($productComponents as $component)
                        <option value="{{ $component['component_product_type_id'] }}"
                                data-stock="{{ $component['available_stock'] }}"
                                data-article="{{ $component['article_no'] }}"
                                data-name="{{ $component['product_name'] }}"
                                data-size="{{ $component['size_name'] }}"
                                data-required="{{ $component['quantity'] }}">
                          {{ $component['article_no'] }} - {{ $component['product_name'] }} ({{ $component['size_name'] }}) - Req: {{ $component['quantity'] }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Available Stock</label>
                    <input type="text" class="form-control" id="available_component_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" step="0.001" class="form-control" name="quantityComponent" id="quantityComponent" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnComponent" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              @endif

              <!-- Product Issuance Section -->
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="stage_id" id="stage_id" multiple>
                      <option value="" disabled>Select Stage</option>
                      @foreach($stagesAll as $stage)
                        <option value="{{ $stage->head_id }}">{{ $stage->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Available Stock</label>
                    <input type="text" class="form-control" id="available_pstock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityStage" id="quantityStage" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnStage" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Material / Stage</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                  </div>
                </div>
              </div>

              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Create PTC</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// PTC Page Variables - used by custom.js PTC Script
var isPtcPage = true;
var isPtcIssuancePage = true;
var stockData = @json($stock);
var pstockData = @json($pstock);
var ptcProductTypeId = '{{ $product_type_id }}';
var ptcProductName = '{{ $productType ? $productType->name . " - " . $productType->size_name : "N/A" }}';
var orderId = '{{ $order_id ?? 0 }}';

// AJAX URLs for material quantity tracking
var ajaxMQtyUrl = "{{ route('ajaxMQty') }}";
var ajaxAMQtyUrl = "{{ route('ajaxAMQty') }}";
var ajaxATMQtyUrl = "{{ route('ajaxATMQty') }}";

// Product Components handling
var componentStockInfo = {};
@if(isset($productComponents) && $productComponents->count() > 0)
@foreach($productComponents as $component)
componentStockInfo['{{ $component['component_product_type_id'] }}'] = {
    stock: {{ $component['available_stock'] }},
    name: '{{ $component['product_name'] }}',
    article_no: '{{ $component['article_no'] }}',
    size_name: '{{ $component['size_name'] }}',
    required_qty: {{ $component['quantity'] }}
};
@endforeach
@endif

$(document).ready(function() {
    // Function to update serial numbers
    function updateComponentSerialNumbers() {
        $('#items-table tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Component selection handler
    $('#component_id').on('change', function() {
        var componentId = $(this).val();
        if (componentId && componentStockInfo[componentId]) {
            $('#available_component_stock').val(componentStockInfo[componentId].stock);
        } else {
            $('#available_component_stock').val('');
        }
    });

    // Add component button handler
    $('#addBtnComponent').click(function() {
        var componentId = $('#component_id').val();
        var componentText = $('#component_id option:selected').text();
        var quantity = parseFloat($('#quantityComponent').val());
        var availableStock = parseFloat($('#available_component_stock').val());

        if (!componentId || !quantity) return;

        if (quantity > availableStock) {
            alert("Quantity cannot be greater than available stock.");
            return;
        }

        // Check for duplicates
        var isDuplicate = false;
        $('#items-table tbody tr').each(function() {
            var existingComponentId = $(this).find('input[name="component_id[]"]').val();
            if (existingComponentId === componentId) {
                isDuplicate = true;
                return false;
            }
        });

        if (isDuplicate) {
            alert("This product component is already added to the table.");
            return;
        }

        var updatedStock = availableStock - quantity;
        $('#available_component_stock').val(updatedStock);
        componentStockInfo[componentId].stock = updatedStock;

        var componentInfo = componentStockInfo[componentId];
        var srNo = $('#items-table tbody tr').length + 1;

        var markup = `<tr>
            <td>${srNo}</td>
            <td>${ptcProductName}<input type="hidden" name="product_type_id[]" value="${ptcProductTypeId}"><input type="hidden" name="stage_id[]" value="0"><input type="hidden" name="component_id[]" value="${componentId}"></td>
            <td><span class="badge badge-warning">Component:</span> ${componentInfo.article_no} - ${componentInfo.name} (${componentInfo.size_name})<input type="hidden" name="material_id[]" value="0"></td>
            <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
            <td><button type="button" class="btn btn-danger deleteComponentRow">X</button></td>
        </tr>`;

        $('#items-table tbody').append(markup);

        $('#component_id').val(null).trigger('change');
        $('#quantityComponent').val('');
        $('#available_component_stock').val('');
        updateComponentSerialNumbers();
    });

    // Delete component row
    $(document).on('click', '.deleteComponentRow', function() {
        var row = $(this).closest('tr');
        var componentId = row.find('input[name="component_id[]"]').val();
        var quantity = parseFloat(row.find('input[name="quantity[]"]').val());

        // Restore stock
        if (componentId && componentStockInfo[componentId]) {
            componentStockInfo[componentId].stock += quantity;
        }

        row.remove();
        updateComponentSerialNumbers();
    });
});
</script>
@endsection


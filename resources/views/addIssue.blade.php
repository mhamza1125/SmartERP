@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issue Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stock.store') }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Issuance No</label>
                    <input type="text" class="form-control" name="stock_no" required value="{{ old('stock_no') }}" placeholder="Issue No">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Issuance No</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Employee</label>
                    <select class="form-control select2" name="employee_id" required>
                      <option value="" selected disabled>Select Employee</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option value="{{$item->employee_id}}" {{ old('employee_id') == $item->employee_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}} {{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Issue Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{old('stock_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      <option value="0" selected disabled>Default Purchase</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ old('order_id') == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id[]" id="product_type_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled selected>Select Product</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                  </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id[]" id="material_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled selected>Select Material</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                  </select>
                  </div>
                </div>
                <div class="col-md-3">                  
                  <div class="form-group">
                    <label for="available_stock">Available Stock</label>
                    <input type="text" class="form-control" id="available_stock" name="available_stock" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantity" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtn" class="btn btn-primary">Add</button>
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
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="row">
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
<script>
  $(document).ready(function() {
    // Function to initialize select2
    function initializeSelect2() {
        $('.select2').select2();
    }

    // Function to update available stock
    function updateAvailableStock(materialId) {
        // Calculate total quantity of the same material present in the table
        var totalQuantityInTable = 0;
        $('#items-table tbody tr').each(function() {
            var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
            if (rowMaterialId === materialId) {
                totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val());
            }
        });

        // Find the stock item for the selected material
        var stockItem = stockData.find(item => item.material_id == materialId);
        if (stockItem) {
            // Calculate available stock by subtracting total quantity in table from total received
            var availableStock = stockItem.total_received - stockItem.total_returned - totalQuantityInTable;
            $('#available_stock').val(availableStock);
        } else {
            // If no stock item found, set available stock to 0
            $('#available_stock').val(0);
        }
    }

    // Event listener for change in order ID
    $('#order_id').on('change', function() {
        if ($('#items-table tbody tr').length > 0) {
            if (!confirm('Changing the order will clear the table. Are you sure you want to proceed?')) {
                $(this).val($(this).data('previous')).trigger('change.select2');
                return;
            }
        }

        $('#items-table tbody').empty();

        var orderId = $(this).val();
        $.ajax({
            url: "{{ route('ajaxPT') }}",
            type: "GET",
            data: { orderId: orderId },
            dataType: "json",
            success: function(response) {
                $('#product_type_id').empty().append('<option value="" disabled selected>Select Product</option>');
                response.data.forEach(function(item) {
                    var optionText = item.article_no + ' - Size ' + item.hname;
                    $('#product_type_id').append(new Option(optionText, item.product_type_id));
                });
                $('#product_type_id').trigger('change');
            }
        });
    });

    // Event listener for change in product type
    $('#product_type_id').on('change', function() {
        var productId = $(this).val();
        $.ajax({
            url: "{{ route('ajaxPM') }}",
            type: "GET",
            data: { productId: productId },
            dataType: "json",
            success: function(response) {
                $('#material_id').empty().append('<option value="" disabled selected>Select Material</option>');
                response.data.forEach(function(item) {
                    $('#material_id').append(new Option(item.name, item.material_id));
                });
                $('#material_id').trigger('change');
            }
        });
    });

    // Event listener for select2:select event on material ID
    $('#material_id').on('select2:select', function(e) {
        var selectedMaterialId = e.params.data.id;
        updateAvailableStock(selectedMaterialId);
    });

    // Event listener for click on add button
    $('#addBtn').click(function() {
        var productId = $('#product_type_id').val();
        var materialId = $('#material_id').val();
        var materialText = $('#material_id option:selected').text();
        var productName = $('#product_type_id option:selected').text();
        var quantity = parseInt($('input[name="quantity"]').val());
        var availableStock = parseInt($('#available_stock').val());

        if (!materialId || !quantity) return;

        if (quantity > availableStock) {
            alert("Quantity cannot be greater than available stock.");
            return;
        }

        var isDuplicate = false;
        $('#items-table tbody tr').each(function() {
            var existingProductId = $(this).find('input[name="product_type_id[]"]').val();
            var existingMaterialId = $(this).find('input[name="material_id[]"]').val();
            if (existingProductId === productId && existingMaterialId === materialId) {
                isDuplicate = true;
                return false;
            }
        });

        if (isDuplicate) {
            alert("This combination of product and material is already added to the table.");
            return;
        }

        var updatedStock = availableStock - quantity;
        $('#available_stock').val(updatedStock);

        var srNo = $('#items-table tbody tr').length + 1;

        var markup = `<tr>
            <td>${srNo}</td>
            <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"></td>
            <td>${materialText}<input type="hidden" name="material_id[]" value="${materialId}"></td>
            <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
            <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
        </tr>`;

        $('#items-table tbody').append(markup);

        $('#material_id').val(null).trigger('change');
        $('input[name="quantity"]').val('');
    });

    // Event listener for click on delete button in table row
    $('#items-table').on('click', '.deleteRow', function() {
        var quantityToRemove = parseInt($(this).closest('tr').find('input[name="quantity[]"]').val());
        var currentAvailableStock = parseInt($('#available_stock').val());
        var updatedStock = currentAvailableStock + quantityToRemove;
        $('#available_stock').val(updatedStock);
        $(this).closest('tr').remove();
    });

    // Initialize select2
    initializeSelect2();

    // Your stockData variable here
    var stockData = @json($stock);
});
</script>
@endsection
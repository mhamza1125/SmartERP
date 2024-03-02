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
            <form action="{{ route('return.store') }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Issuance No</label>
                    <input type="text" class="form-control" name="issuance_no" required value="{{ old('issuance_no') }}" placeholder="Return No">
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
                    <input type="text" class="form-control datepicker" name="issue_date" required value="{{old('issue_date')}}">
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
                    <input type="number" min="0" class="form-control" name="quantity" required placeholder="0">
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
          $('#product_type_id').trigger('change'); // Update select2
        }
      });
    });

    $('.select2').select2(); // Initialize select2
    
    // Load materials based on the selected product
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
          $('#material_id').trigger('change'); // Update select2
        }
      });
    });

    // Update available stock when material changes
    $('#material_id').on('select2:select', function(e) {
      var selectedMaterialId = e.params.data.id;
      updateAvailableStock(selectedMaterialId);
    });

    // Function to update available stock
    function updateAvailableStock(materialId) {
      var stockItem = stockData.find(item => item.material_id == materialId);
      var availableStock = stockItem ? stockItem.total_received - stockItem.total_returned : 0;
      $('#available_stock').val(availableStock);
    }

    // Adding materials to the table with quantities
    $('#addBtn').click(function() {
      var productId = $('#product_type_id').val(); // Get the selected product ID
      var materialId = $('#material_id').val();
      var materialText = $('#material_id option:selected').text();
      var productName = $('#product_type_id option:selected').text(); // Get the selected product name
      var quantity = parseInt($('input[name="quantity"]').val());
      var availableStock = parseInt($('#available_stock').val());

      if (!materialId || !quantity) return; // Prevent adding if fields are empty

      if (quantity > availableStock) {
        alert("Quantity cannot be greater than available stock.");
        return;
      }

      // Check if the material is already added for the selected product
      var isAdded = $('#items-table tbody').find('tr').toArray().some(function(row) {
        var existingProductMaterialId = $(row).find('input[name="product_material_id[]"]').val();
        return existingProductMaterialId === productId + '-' + materialId;
      });

      if (isAdded) {
        alert("This material is already added for the selected product.");
        return;
      }

      // Generate serial number for the new row
      var srNo = $('#items-table tbody tr').length + 1;

      // Add the row to the table
      var markup = `<tr>
        <td>${srNo}</td>
        <td>${productName}<input type="hidden" name="product_type_id[]" value="${productId}"></td>
        <td>${materialText}<input type="hidden" name="material_id[]" value="${materialId}"></td>
        <td>${quantity}<input type="hidden" name="quantity[]" value="${quantity}"></td>
        <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
      </tr>`;

      $('#items-table tbody').append(markup);

      // Reset the form fields
      $('#material_id').val(null).trigger('change');
      $('input[name="quantity"]').val('');
    });

    // Remove a row from the table
    $('#items-table').on('click', '.deleteRow', function() {
      $(this).closest('tr').remove();
    });

    // Function to update available stock
    function updateAvailableStock(materialId) {
      var stockItem = stockData.find(item => item.material_id == materialId);
      var availableStock = stockItem ? stockItem.total_received - stockItem.total_returned : 0;
      $('#available_stock').val(availableStock);
    }
    
    // Your stockData variable here
    var stockData = @json($stock);
  });
</script>
@endsection
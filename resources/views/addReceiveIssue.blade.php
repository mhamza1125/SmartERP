@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Issuance</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('rstock.store', $issue['stock_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Receiving Issuance No</label>
                    <input type="hidden" name="stock_type" required value="1">
                    <input type="hidden" name="receive_issue_id" required value="{{$issue['stock_id']}}">
                    <input type="text" class="form-control" name="stock_no" required value="{{$issue['stock_no']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <input type="hidden" name="order_id" required value="{{$issue['order_id']}}">
                    <input type="text" class="form-control" required value="{{$issue['job_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee</label>
                    <input type="hidden" name="employee_id" required value="{{$issue['employee_id']}}">
                    <input type="text" class="form-control" required value="{{$issue['employee_no']}} - {{$issue['name']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Receiving Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{old('stock_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id[]" id="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($issueItem->count())
                          @foreach($issueItem as $item)
                              @if($item->material_id)
                                <option value="{{$item->material_id}}|{{$item->product_type_id}}">{{$item->name}} | {{$item->article_no}} - Size {{$item->sname}}</option>
                              @endif
                          @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">                  
                  <div class="form-group">
                    <label for="receiveable_stock">Receiveable Stock</label>
                    <input type="text" class="form-control" id="receiveable_stock" name="receiveable_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityMaterial" placeholder="0" id="quantityMaterial">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnMaterial" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id[]" id="product_type_id">
                      <option value="" disabled selected>Select Product</option>
                      @if($issueItem->count())
                        @php $issueItemUnique = $issueItemUnique->unique('product_type_id'); @endphp
                        @foreach($issueItemUnique as $item)
                        <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->article_no}} - Size {{$item->sname}}</option>
                        @endforeach
                      @endif
                  </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="stage_id[]" id="stage_id" required>
                      <option value="" selected disabled>Select Product Stage</option>
                      @if($head->count())
                        @foreach($head as $item)
                          <option value="{{$item->head_id}}" {{ old('head_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityProduct" placeholder="0" id="quantityProduct">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnProduct" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Receive Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">Issued Items</a>
                    </li>
                  </ul>     
                  <div class="tab-content" id="myTabContent">
                    {{-- Receive Issuance --}}
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
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
                        <tbody>
                        </tbody>
                        <tfoot>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                            <th>Action</th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    {{-- Issuance --}}
                    <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                      <table class="table table-sm table-striped">                    
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueItem->count())
                            @foreach($issueItem as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                <td>{{($item->name)? $item->name:$item->stage}}</td>
                                <td>{{$item->quantity}} {{$item->uname}}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                        <tfoot>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description"></textarea>
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
      var issueItems = @json($issueItem);
  
      // Function to initialize select2
      function initializeSelect2() {
          $('.select2').select2();
      }
  
      // Event listener for change in material select element
      $('#material_id').change(function() {
          var selectedOption = $(this).val();
          if (!selectedOption) {
              return;
          }
          var parts = selectedOption.split('|');
          var materialId = parts[0];
          var productId = parts[1];
          updateAvailableStock(materialId, productId);
      });
  
      // Function to update available stock
      function updateAvailableStock(materialId, productId) {
          var totalQuantityInTable = 0;
          $('#items-table tbody tr').each(function() {
              var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
              var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
              
              // Checking both material ID and product ID to accurately identify the row
              if (rowMaterialId === materialId && rowProductId === productId) {
                  totalQuantityInTable += parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
              }
          });

          // Find the issueItem based on both material_id and product_type_id
          var issueItem = issueItems.find(item => item.material_id.toString() === materialId && item.product_type_id.toString() === productId);
          if (issueItem) {
              var availableStock = issueItem.quantity - totalQuantityInTable;
              $('#receiveable_stock').val(availableStock > 0 ? availableStock : 0);
          } else {
              $('#receiveable_stock').val(0);
          }
      }

  
      // Event listener for click on add button in product section
      $('#addBtnProduct').click(function() {
          var productId = $('#product_type_id').val();
          var productName = $('#product_type_id option:selected').text();
          var stageId = $('#stage_id').val();
          var stageName = $('#stage_id option:selected').text();
          var quantity = parseInt($('input[name="quantityProduct"]').val());
  
          if (!productId || !quantity || !stageId) {
              alert("Please select a product and specify its quantity and stage.");
              return;
          }
  
          if (isProductStageCombinationExists(productId, stageId)) {
              alert("This product and stage combination already exists in the table.");
              return;
          }
  
          var srNo = $('#items-table tbody tr').length + 1;
  
          var markup = `<tr>
              <td>${srNo}</td>
              <td>${productName}<input type="text" name="product_type_id[]" value="${productId}"><input type="text" name="material_id[]" value="0"></td>
              <td>${stageName}<input type="text" name="stage_id[]" value="${stageId}"></td>
              <td>${quantity}<input type="text" name="quantity[]" value="${quantity}"></td>
              <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
          </tr>`;
  
          $('#items-table tbody').append(markup);
  
          $('input[name="quantityProduct"]').val('');
          updateSerialNumbers();
      });
  
      // Event listener for click on add button in material section
      $('#addBtnMaterial').click(function() {
          var selectedOption = $('#material_id option:selected').val();
          var selectedValues = selectedOption.split('|');
          var materialId = selectedValues[0];
          var productId = selectedValues[1];
          var materialText = $('#material_id option:selected').text();
          var materialValues = materialText.split('|');
          var materialName = materialValues[0];
          var productName = materialValues[1];
          var quantity = parseInt($('input[name="quantityMaterial"]').val());
          var availableStock = parseInt($('#receiveable_stock').val());
          var stageId = '0';
          // var stageName = 'Raw Material'; 
          // var stageId = $('#stage_id option:first').val();
          // var stageName = $('#stage_id option:first').text(); 

          if (!materialId || !quantity) return;

          if (quantity > availableStock) {
              alert("Quantity cannot be greater than available stock.");
              return;
          }

          if (isMaterialExists(materialId, productId)) {
              alert("This material for the selected product already exists in the table.");
              return;
          }

          var srNo = $('#items-table tbody tr').length + 1;

          var markup = `<tr>
              <td>${srNo}</td>
              <td>${productName}<input type="text" name="material_id[]" value="${materialId}"><input type="text" name="product_type_id[]" value="${productId}"></td>
              <td>${materialName}<input type="text" name="stage_id[]" value="${stageId}"></td>
              <td>${quantity}<input type="text" name="quantity[]" value="${quantity}"></td>
              <td><button type="button" class="btn btn-danger deleteRow">X</button></td>
          </tr>`;

          $('#items-table tbody').append(markup);

          var updatedStock = availableStock - quantity;
          $('#receiveable_stock').val(updatedStock);

          $('input[name="quantityMaterial"]').val('');
          updateSerialNumbers();
      });
  
      // Function to update serial numbers
      function updateSerialNumbers() {
          $('#items-table tbody tr').each(function(index) {
              $(this).find('td:first').text(index + 1);
          });
      }
  
      // Event listener for click on delete button in table row
      $('#items-table').on('click', '.deleteRow', function() {
          var quantityToRemove = parseInt($(this).closest('tr').find('input[name="quantity[]"]').val());
          var currentAvailableStock = parseInt($('#receiveable_stock').val());
          var updatedStock = currentAvailableStock + quantityToRemove;
          $('#receiveable_stock').val(updatedStock);
          $(this).closest('tr').remove();
          updateSerialNumbers();
      });
  
      // Initialize select2
      initializeSelect2();
  
      // Your stockData variable here
      var issueItems = @json($issueItem);
  
      // Function to check if product and stage combination already exists
      function isProductStageCombinationExists(productId, stageId) {
          var exists = false;
          $('#items-table tbody tr').each(function() {
              var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
              var rowStageId = $(this).find('input[name="stage_id[]"]').val();
              if (rowProductId == productId && rowStageId == stageId) {
                  exists = true;
                  return false; // exit loop early
              }
          });
          return exists;
      }

      // Function to check if material with the specified product already exists
      function isMaterialExists(materialId, productId) {
          var exists = false;
          $('#items-table tbody tr').each(function() {
              var rowMaterialId = $(this).find('input[name="material_id[]"]').val();
              var rowProductId = $(this).find('input[name="product_type_id[]"]').val();
              
              // Check combination of material ID and product ID
              if (rowMaterialId === materialId && rowProductId === productId) {
                  exists = true;
                  return false; // exit loop early
              }
          });
          return exists;
      }
  });
</script>  
@endsection
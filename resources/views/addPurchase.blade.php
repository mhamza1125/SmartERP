@extends('index')
@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Purchase</h4>
            <div class="card-header-action">
              <a href="{{ route('purchase') }}" class="btn btn-primary">
                View All
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('purchase.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase No</label>
                    <input type="text" class="form-control" name="purchase_no" required value="{{old('purchase_no')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Purchase No</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Vendor</label>
                    <select class="form-control select2" name="vendor_id" required>
                      <option selected disabled>Select Vendor</option>
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option value="{{$item->vendor_id}}" {{ old('vendor_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->fname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Vendor</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <select class="form-control select2" name="order_id" required>
                      <option value="0" selected disabled>Default Purchase</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ old('order_id') == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="text" class="form-control datepicker" name="purchase_date" required value="{{old('purchase_date')}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Puchase Date</div>
                  </div>
                </div>
              </div>

              <h6>Purchase Items</h6>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Materisls</label>
                    <select class="form-control select2" name="material_id[]" required>
                      <option disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantity" required value="{{old('quantity')}}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" min="0" class="form-control" name="price" required value="{{old('price')}}">
                  </div>
                </div>
                <div class="col-md-2">
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
                        <th>Item / Material</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
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
    var tableRowCount = 1;

    // Initially disable the add button
    $('#addBtn').prop('disabled', true);

    // Function to check if all three fields have data
    function checkFields() {
      var materialId = $('select[name="material_id[]"]').val();
      var quantity = $('input[name="quantity"]').val();
      var price = $('input[name="price"]').val();
      return (materialId && quantity && price);
    }

    // Enable/disable add button based on field values
    $('select[name="material_id[]"], input[name="quantity"], input[name="price"]').on('change keyup', function() {
      $('#addBtn').prop('disabled', !checkFields());
    });

    $('#addBtn').on('click', function() {
      var materialId = $('select[name="material_id[]"]').val();
      var materialName = $('select[name="material_id[]"] option:selected').text();
      var quantity = $('input[name="quantity"]').val();
      var price = $('input[name="price"]').val();
      var total = quantity * price;

      // Add row to table
      var newRow = '<tr>' +
        '<td>' + tableRowCount + '</td>' +
        '<td>' + materialName + '<input type="hidden" name="material_name[]" value="' + materialName + '"><input type="hidden" name="material_id[]" value="' + materialId + '"></td>' +
        '<td>' + quantity + '<input type="hidden" name="quantity[]" value="' + quantity + '"></td>' +
        '<td>' + price + '<input type="hidden" name="price[]" value="' + price + '"></td>' +
        '<td>' + total + '<input type="hidden" name="total[]" value="' + total + '"></td>' +
        '<td><button class="deleteRowBtn btn btn-danger">X</button></td>' +
        '</tr>';
      
      $('#items-table tbody').append(newRow);

      tableRowCount++;
      
      // Disable Btn & Reset input field
      $('#addBtn').prop('disabled', true); 
      $('input[name="quantity"]').val('0');
      $('input[name="price"]').val('0');
    });

    // Delete row when delete button is clicked
    $(document).on('click', '.deleteRowBtn', function() {
      $(this).closest('tr').remove();
    });


    $('#submitBtn').on('click', function() {
      // Gather data from table and submit
      var tableData = [];
      $('#items-table tbody tr').each(function(index, row) {
        var rowData = {
          'material_id': $(row).find('input[name="material_id[]"]').val(),
          'material_name': $(row).find('input[name="material_name[]"]').val(),
          'quantity': $(row).find('input[name="quantity[]"]').val(),
          'price': $(row).find('input[name="price[]"]').val(),
          'total': $(row).find('input[name="total[]"]').val()
        };
        tableData.push(rowData);
      });
    });
  });
</script>
@endsection
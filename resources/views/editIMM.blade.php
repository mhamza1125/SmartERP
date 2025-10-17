@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Issue Machine Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stock.update', $issue['stock_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-2 dnone">
                  <div class="form-group">
                    <label>Issuance No</label>
                    <input type="hidden" name="stock_type" required value="2">
                    <input type="hidden" name="stock_status" required value="4">
                    <input type="hidden" name="issue_for" required value="0">
                    <input type="hidden" name="order_id" required value="0">
                    <input type="hidden" id="table_name" name="table_name" value="employee">
                    <input type="hidden" name="product_type_id[]" value="0">
                    <input type="hidden" name="stage_id[]" value="0">
                    <input type="text" class="form-control" name="stock_no" required value="{{$issue['stock_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Issuance No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Machine</label>
                    <select class="form-control select2" name="machine_id" required>
                      <option value="" selected disabled>Select Machine</option>
                      @if($machine->count())
                        @foreach($machine as $item)
                          <option value="{{$item->machine_id}}" {{ $issue['machine_id'] == $item->machine_id ? 'selected' : '' }}>{{$item->machine_no}} | {{$item->name ? $item->employee_no .' - '. $item->name : 'Not Asigned'}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Machine</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">                    
                    <label>Employee</label>
                    <select class="form-control select2" name="employee_id" id="employee_id" required>
                      <option value="" selected disabled>Select Employee</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option data-type="employee" value="{{$item->employee_id}}" {{ ($issue['table_name'] == 'employee' && $issue['employee_id'] == $item->employee_id) ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                      {{-- @if($vendor->count())
                        @foreach($vendor as $item)
                          <option data-type="vendor" value="{{$item->vendor_id}}" {{ old('vendor_id') == $item->vendor_id ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                        @endforeach
                      @endif --}}
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee</div>
                  </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Issue Date</label>

                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{$issue['stock_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="smaterial_id[]" id="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($material->count())
                        @foreach($material as $item)
                          <option value="{{$item->material_id}}" data-stock="{{$item->available_stock}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>
                            {{$item->material_no}} - {{$item->name}}
                          </option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="available_stock">Available Stock</label>
                    <input type="text" class="form-control" id="available_stock" name="available_stock" readonly>
                  </div>
                </div> 
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityMaterial" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnMM" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($issueItem->count())
                        @foreach($issueItem as $item)
                          <tr data-item-id="{{ $item->purchase_item_id }}">
                            <td></td>
                            <td>{{$item->name}}
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
                            </td>
                            <td>{{$item->quantity}}
                              <input type="hidden" name="quantity[]" value="{{$item->quantity}}"></td>
                            </td>
                            <td>
                              <button class="deleteRow btn btn-danger">X</button>
                            </td>
                          </tr>
                          <tr id="hiddentr" class="dnone">
                            <td colspan="5">
                              <input type="hidden" name="hidden_material_id[]" value="{{$item->material_id}}">
                              <input type="hidden" name="hidden_quantity[]" value="{{$item->quantity}}">
                            </td>                            
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
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
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Submit</button>
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
  var isIssuePage = false;
  var stockData = @json($stock);
  // Following are needed in other Issuance Page
  var pstockData = @json($pstock);
  // var ajaxPTUrl = "{{ route('ajaxPT') }}";
  var ajaxPTUrl = "{{ route('ajaxPTStock') }}";
  var ajaxPMUrl = "{{ route('ajaxPM') }}";
  var ajaxMQtyUrl = "{{ route('ajaxMQty') }}";
</script>
@endsection
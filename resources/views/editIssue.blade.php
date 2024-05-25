@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Issue Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stock.update', $issue['stock_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Issuance No</label>
                    <input type="hidden" name="stock_type" required value="2">
                    <input type="hidden" id="table_name" name="table_name" value="{{$issue['table_name']}}">
                    <input type="text" class="form-control" name="stock_no" required value="{{$issue['stock_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Issuance No</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">                    
                    <label>Issuance For</label>
                    <select class="form-control select2" name="issue_for" required>
                      <option value="" selected disabled>Select Stage</option>
                      @if($stage->count())
                        @foreach($stage as $item)
                          <option value="{{$item->head_id}}" {{ $issue['issue_for'] == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Product Stage</div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Employee</label>
                    <select class="form-control select2" name="employee_id" id="employee_id" required>
                      <option value="" selected disabled>Select Employee / Vendor</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option data-type="employee" value="{{$item->employee_id}}" 
                                  {{ ($issue['table_name'] == 'employee' && $issue['employee_id'] == $item->employee_id) ? 'selected' : '' }}>
                            {{$item->employee_no}} - {{$item->name}}
                          </option>
                        @endforeach
                      @endif
                      @if($vendor->count())
                        @foreach($vendor as $item)
                          <option data-type="vendor" value="{{$item->vendor_id}}" 
                                  {{ ($issue['table_name'] == 'vendor' && $issue['employee_id'] == $item->vendor_id) ? 'selected' : '' }}>
                            {{$item->vendor_no}} - {{$item->fname}}
                          </option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Employee / Vendor</div>
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
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Issuance For Orders</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      <option value="" selected disabled>Select Order</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ $issue['order_id'] == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id" id="product_type_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled>Select Product</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                  </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id" id="material_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled selected>Select Material</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
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
                    <label for="available_stock">Total Req &nbsp|&nbsp Issued &nbsp|&nbsp To Issue</label>  
                    <input type="text" class="form-control" id="materialQty" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityMaterial" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnMaterial" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              {{-- <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="available_stock">Total Required</label>  
                    <input type="text" class="form-control" id="materialQty0" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="available_stock">Issued</label>  
                    <input type="text" class="form-control" id="materialQty1" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="available_stock">To Issue</label>  
                    <input type="text" class="form-control" id="materialQty2" readonly>
                  </div>
                </div>
              </div> --}}

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select2" name="stage_id" id="stage_id" multiple>
                      <!-- Options will be dynamically added here via JavaScript -->
                      {{-- <option value="" disabled>Select Product</option> --}}
                      <!-- You can keep this option or remove it, depending on your needs -->
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="available_pstock">Available Stock</label>
                    <input type="text" class="form-control" id="available_pstock" name="available_pstock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityStage" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
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
                    <tbody>
                      @if($issueItem->count())
                        @foreach($issueItem as $item)
                          <tr data-item-id="{{ $item->purchase_item_id }}">
                            <td></td>
                            <td>{{$item->article_no}} - Size {{$item->sname}}
                              <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}">
                              <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}">
                            </td>
                            <td>@if($item->material_id){{$item->name}}
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
                              @else{{$item->stage}}<input type="hidden" name="material_id[]" value="0">@endif
                            </td>
                            <td>{{$item->quantity}}
                              <input type="hidden" name="quantity[]" value="{{$item->quantity}}"></td>
                            </td>
                            <td>@if($item->material_id)
                              <button class="deleteRow btn btn-danger">X</button>
                              @else <button class="deletepRow btn btn-danger">X</button>@endif</td>
                          </tr>
                          <tr id="hiddentr" class="dnone">
                            <td colspan="5">
                              <input type="hidden" name="hidden_product_type_id[]" value="{{$item->product_type_id}}">
                              <input type="hidden" name="hidden_stage_id[]" value="{{$item->stage_id}}">
                              @if($item->material_id)
                              <input type="hidden" name="hidden_material_id[]" value="{{$item->material_id}}">
                              @else<input type="hidden" name="hidden_material_id[]" value="0">@endif
                              <input type="hidden" name="hidden_quantity[]" value="{{$item->quantity}}">
                            </td>                            
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
                    <textarea class="summernote" name="description">{{$issue['description']}}</textarea>
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
  var isIssuePage = true;
  var stockData = @json($stock);
  var pstockData = @json($pstock);
  var ajaxPTUrl = "{{ route('ajaxPT') }}";
  var ajaxPMUrl = "{{ route('ajaxPM') }}";
  var ajaxMQtyUrl = "{{ route('ajaxMQty') }}";
</script>
@endsection
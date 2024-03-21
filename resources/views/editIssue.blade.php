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
            <form action="{{ route('stock.update', $issue['stock_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Issuance No</label>
                    <input type="hidden" name="stock_type" required value="2">
                    <input type="text" class="form-control" name="stock_no" required value="{{$issue['stock_no']}}" placeholder="Issue No">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Issuance No</div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Employee</label>
                    <select class="form-control select2" name="employee_id" required>
                      <option value="" selected disabled>Select Employee</option>
                      @if($employee->count())
                        @foreach($employee as $item)
                          <option value="{{$item->employee_id}}" {{ $issue['employee_id'] == $item->employee_id ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}} {{$item->fname}}</option>
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
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{$issue['issue_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      <option value="0" selected disabled>Default Purchase</option>
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
                      <option value="" disabled selected>Select Product</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                  </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id" id="material_id">
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
                <div class="col-md-3">
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

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select2" name="stage_id" id="stage_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled selected>Select Product</option>
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
                              <input type="text" name="product_type_id[]" value="{{$item->product_type_id}}">
                              <input type="text" name="stage_id[]" value="{{$item->stage_id}}">
                            </td>
                            <td>@if($item->material_id){{$item->name}}
                              <input type="text" name="material_id[]" value="{{$item->material_id}}">
                              @else{{$item->stage}}<input type="text" name="material_id[]" value="0">@endif
                            </td>
                            <td>{{$item->quantity}}
                              <input type="text" name="quantity[]" value="{{$item->quantity}}"></td>
                            </td>
                            <td>@if($item->material_id)
                              <button class="deleteRow btn btn-danger">X</button>
                              @else <button class="deletepRow btn btn-danger">X</button>@endif</td>
                          </tr>
                          <tr id="hiddentr" class="dnone">
                            <td colspan="5">
                              <input type="text" name="hidden_product_type_id[]" value="{{$item->product_type_id}}">
                              <input type="text" name="hidden_stage_id[]" value="{{$item->stage_id}}">
                              @if($item->material_id)
                              <input type="text" name="hidden_material_id[]" value="{{$item->material_id}}">
                              @else<input type="text" name="hidden_material_id[]" value="0">@endif
                              <input type="text" name="hidden_quantity[]" value="{{$item->quantity}}">
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
  var isIssuePage = true;
  var stockData = @json($stock);
  var pstockData = @json($pstock);
  var ajaxPTUrl = "{{ route('ajaxPT') }}";
  var ajaxPMUrl = "{{ route('ajaxPM') }}";
</script>

@endsection
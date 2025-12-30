@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Process Travel Card (PTC)</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.update', $ptc->stock_id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>PTC No</label>
                    <input type="text" class="form-control" value="PTC-{{ $ptc->stock_no }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order</label>
                    <input type="text" class="form-control" value="{{ $ptc->job_no ?? 'Default PTC' }}" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Product</label>
                    <input type="text" class="form-control" value="{{ $product ? $product->name . ' - ' . $product->size_name : 'N/A' }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>PTC Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{ $ptc->stock_date }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Start Stage</label>
                    <input type="text" class="form-control" value="{{ $startStage ? $startStage->name : 'N/A' }}" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>End Stage</label>
                    <input type="text" class="form-control" value="{{ $endStage ? $endStage->name : 'N/A' }}" readonly>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Product Quantity</label>
                    <input type="number" min="1" class="form-control" name="product_quantity" required value="{{ $ptc->product_quantity ?? 1 }}">
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
                      </div>
                      @if($index < count($stages) - 1)
                        <div class="mr-2 mb-2 p-2"><i class="fas fa-arrow-right"></i></div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>

              <hr>
              <h5>Issued Items</h5>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Employee / Vendor</label>
                    <select class="form-control select2" name="employee_id" id="employee_id">
                      <option value="" selected disabled>Select Employee / Vendor</option>
                      @if($employees->count())
                        @foreach($employees as $item)
                          <option data-type="employee" value="{{ $item->employee_id }}" {{ $ptc->employee_id == $item->employee_id && $ptc->table_name == 'employee' ? 'selected' : '' }}>{{ $item->employee_no }} - {{ $item->name }}</option>
                        @endforeach
                      @endif
                      @if($vendors->count())
                        @foreach($vendors as $item)
                          <option data-type="vendor" value="{{ $item->vendor_id }}" {{ $ptc->employee_id == $item->vendor_id && $ptc->table_name == 'vendor' ? 'selected' : '' }}>{{ $item->vendor_no }} - {{ $item->fname }}</option>
                        @endforeach
                      @endif
                    </select>
                    <input type="hidden" id="table_name" name="table_name" value="{{ $ptc->table_name }}">
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
                        <option value="{{ $material->material_id }}">{{ $material->name }} | {{ $material->unit }}</option>
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

              <!-- Product Issuance Section -->
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="stage_id" id="stage_id" multiple>
                      <option value="" disabled>Select Stage</option>
                      @foreach($stages as $stage)
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
                    <tbody>
                      @php $rowIndex = 1; @endphp
                      @foreach($stockItems as $item)
                        <tr>
                          <td>{{ $rowIndex++ }}</td>
                          @if($item->material_id > 0)
                            <td>Material<input type="hidden" name="product_type_id[]" value="0"><input type="hidden" name="stage_id[]" value="0"></td>
                            <td>{{ $item->name }}<input type="hidden" name="material_id[]" value="{{ $item->material_id }}"><input type="hidden" name="material_name[]" value="{{ $item->name }}"></td>
                          @else
                            <td>{{ $product ? $product->name . ' - ' . $product->size_name : 'N/A' }}<input type="hidden" name="product_type_id[]" value="{{ $item->product_type_id }}"><input type="hidden" name="material_id[]" value="0"></td>
                            <td>{{ $item->stage ?? 'N/A' }}<input type="hidden" name="stage_id[]" value="{{ $item->stage_id }}"></td>
                          @endif
                          <td>{{ $item->quantity }}<input type="hidden" name="quantity[]" value="{{ $item->quantity }}"></td>
                          <td><button type="button" class="{{ $item->material_id > 0 ? 'deleteRow' : 'deletepRow' }} btn btn-danger btn-sm">X</button></td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description">{!! $ptc->description !!}</textarea>
                  </div>
                </div>
              </div>

              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Update PTC</button>
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
// PTC Edit Page Variables - used by custom.js PTC Script
var isPtcPage = true;
var stockData = @json($stock);
var pstockData = @json($pstock);
var ptcProductTypeId = '{{ $stockItems->first()->product_type_id ?? 0 }}';
var ptcProductName = '{{ $product ? $product->name . " - " . $product->size_name : "N/A" }}';
</script>
@endsection


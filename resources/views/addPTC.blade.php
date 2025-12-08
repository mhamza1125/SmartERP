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
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Start Stage</label>
                    <input type="text" class="form-control" value="{{ $startStage->name }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>End Stage</label>
                    <input type="text" class="form-control" value="{{ $endStage->name }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Order Quantity</label>
                    <input type="text" class="form-control bg-light" value="{{ $orderQuantity ?? 'N/A' }}" readonly>
                    <small class="text-muted">From order items</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>PTC Quantity <span class="text-danger">*</span></label>
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
                    <tbody></tbody>
                  </table>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{ old('description') }}</textarea>
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
</script>
@endsection


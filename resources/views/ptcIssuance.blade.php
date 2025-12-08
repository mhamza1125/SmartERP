@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-arrow-circle-right text-primary"></i> PTC Issuance</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.issue.store', $ptc->stock_id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf

              <!-- PTC Info -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <strong>PTC No:</strong> PTC-{{ $ptc->stock_no }}
                </div>
                <div class="col-md-3">
                  <strong>Product:</strong> {{ $product ? $product->name . ' - ' . $product->size_name : 'N/A' }}
                </div>
                <div class="col-md-3">
                  <strong>Order:</strong> {{ $ptc->job_no ?? 'Default PTC' }}
                </div>
                <div class="col-md-3">
                  <strong>Current Stage:</strong>
                  <span class="badge badge-primary">{{ $ptc->current_stage_name ?? 'N/A' }}</span>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Issuance Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="stock_date" required value="{{ date('Y-m-d') }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Issue For Stage <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="issue_for" id="issue_for" required>
                      @foreach($stages as $stage)
                        <option value="{{ $stage->head_id }}" {{ $ptc->current_stage_id == $stage->head_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Issue To (Employee / Vendor) <span class="text-danger">*</span></label>
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

              <hr>

              <h5 class="text-primary"><i class="fas fa-upload"></i> Issue Items</h5>

              {{-- Material Issue Section --}}
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="i_material_id" id="material_id">
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

              {{-- Product Issue Section --}}
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

              {{-- Issue Items Table --}}
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

              <hr>

              <!-- Previous Issuances -->
              @if($issuances->count() > 0)
              <h5 class="text-info"><i class="fas fa-history"></i> Previous Issuances</h5>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Reference</th>
                      <th>Stage</th>
                      <th>Date</th>
                      <th>Issued To</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($issuances as $idx => $issuance)
                      <tr>
                        <td>PTC-{{ $ptc->stock_no }} / I{{ str_pad($idx + 1, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $issuance->stage_name ?? 'N/A' }}</td>
                        <td>{{ $issuance->stock_date }}</td>
                        <td>{{ $issuance->employee_name ?? $issuance->vendor_name ?? '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <hr>
              @endif

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                  </div>
                </div>
              </div>

              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">
                    <i class="fas fa-arrow-circle-right"></i> Create Issuance
                  </button>
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
// PTC Issuance Page Variables
var isPtcIssuancePage = true;
var isPtcPage = true;

// Issue section data (for custom.js PTC Script)
var stockData = @json($stock);
var pstockData = @json($pstock);
var ptcProductTypeId = '{{ $product ? $product->product_type_id : 0 }}';
var ptcProductName = '{{ $product ? $product->name . " - " . $product->size_name : "N/A" }}';
var orderId = '{{ $ptc->order_id ?? 0 }}';

// AJAX URLs for material quantity tracking
var ajaxMQtyUrl = "{{ route('ajaxMQty') }}";
var ajaxAMQtyUrl = "{{ route('ajaxAMQty') }}";
var ajaxATMQtyUrl = "{{ route('ajaxATMQty') }}";
</script>
@endsection


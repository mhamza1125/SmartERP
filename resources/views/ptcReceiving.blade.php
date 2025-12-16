@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-arrow-circle-down text-success"></i> PTC Receiving</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.receive.store', $ptc->stock_id) }}" method="POST" class="needs-validation" novalidate="">
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

              @if($issuance ?? false)
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Receiving against Issuance:</strong>
                PTC-{{ $ptc->stock_no }} / I{{ $issuanceSeqNo ?? str_pad($issuance->stock_no, 3, '0', STR_PAD_LEFT) }} | {{ $issuance->employee_name ?? $issuance->vendor_name ?? '-' }}
                <input type="hidden" name="issue_id" value="{{ $issuance->stock_id }}">
              </div>
              @endif

              <hr>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Receiving Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="stock_date" required value="{{ date('Y-m-d') }}"
                      @if($issuance ?? false) min="{{ $issuance->stock_date }}" @endif>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Receive From Stage <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="receive_stage_id" id="receive_stage_id" required>
                      @foreach($stages as $stage)
                        <option value="{{ $stage->head_id }}" {{ $ptc->current_stage_id == $stage->head_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Received From (Employee / Contractor) <span class="text-danger">*</span></label>
                    @if($issuance ?? false)
                      <input type="text" class="form-control" readonly
                        value="{{ $issuance->table_name === 'employee' ? $issuance->employee_no . ' - ' . $issuance->employee_name : $issuance->vendor_no . ' - ' . $issuance->vendor_name }}">
                      <input type="hidden" name="employee_id" value="{{ $issuance->employee_id }}">
                      <input type="hidden" name="table_name" value="{{ $issuance->table_name }}">
                    @else
                      <select class="form-control select2" name="employee_id" id="employee_id" required>
                        <option value="" selected disabled>Select Employee / Contractor</option>
                        @if($employees->count())
                          <optgroup label="Employees">
                          @foreach($employees as $item)
                            <option data-type="employee" value="{{ $item->employee_id }}">{{ $item->employee_no }} - {{ $item->name }}</option>
                          @endforeach
                          </optgroup>
                        @endif
                        @if($vendors->count())
                          <optgroup label="Contractors">
                          @foreach($vendors as $item)
                            <option data-type="vendor" value="{{ $item->vendor_id }}">{{ $item->vendor_no }} - {{ $item->fname }}</option>
                          @endforeach
                          </optgroup>
                        @endif
                      </select>
                      <input type="hidden" id="table_name" name="table_name">
                    @endif
                  </div>
                </div>
              </div>

              <hr>

              <h5 class="text-success"><i class="fas fa-download"></i> Receive Items</h5>

              {{-- Material Receive Section --}}
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="smaterial_id" style="width: 100%" id="r_material_id">
                      <option value="" disabled selected>Select Material</option>
                      @php $lastKey = null; @endphp
                      @if($issueItem->count())
                        @foreach($issueItem as $item)
                          @if($item->material_id)
                            @php $currentKey = $item->product_type_id; @endphp
                            @if($lastKey != $currentKey)
                              @php $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0'; @endphp
                              <option disabled>========== {{$item->article_no}} | Size {{$item->sname}} | Avg {{$minAvg}} ==========</option>
                              @php $lastKey = $currentKey; @endphp
                            @endif
                            <option value="{{$item->material_id}}|{{$item->product_type_id}}">
                              {{$item->name}} | {{ $item->uname ?? 'Unit' }} | Avg {{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0' }}
                            </option>
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
                    <input type="number" min="0" step="0.01" class="form-control" name="r_quantityMaterial" placeholder="0" id="r_quantityMaterial">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnReceiveMaterial" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>

              {{-- Product Receive Section --}}
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="r_product_type_id" id="r_product_type_id">
                      <option value="" disabled selected>Select Product</option>
                      @if($issueItem->count())
                        @php $issueItemUniqueR = $issueItem->unique('product_type_id'); @endphp
                        @foreach($issueItemUniqueR as $item)
                          @php
                            $currentKey = $item->product_type_id;
                            $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0';
                          @endphp
                          <option value="{{$item->product_type_id}}">
                            {{$item->article_no}} | Size {{$item->sname}} | Avg {{$minAvg}}
                          </option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="r_stage_id" id="r_stage_id">
                      <option value="" disabled selected>Select Stage</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Work Log (Cost)</label>
                    <select class="form-control select2" name="r_pcost_id[]" id="r_pcost_id" multiple="">
                      <option value="" disabled>Select Work Log</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="r_quantityProduct" placeholder="0" id="r_quantityProduct">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnReceiveProduct" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>

              {{-- Component Product Type Receive Section --}}
              @if(isset($issuedComponents) && $issuedComponents->count())
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Component Products (Issued)</label>
                    <select class="form-control select2" name="r_component_product_type_id" id="r_component_product_type_id">
                      <option value="" disabled selected>Select Component Product</option>
                      @foreach($issuedComponents as $component)
                        @if($component->receivable_quantity > 0)
                          <option value="{{ $component->component_product_type_id }}"
                            data-receivable="{{ $component->receivable_quantity }}"
                            data-unit="{{ $component->component_unit ?? 'Pcs' }}">
                            {{ $component->component_article_no }} - {{ $component->component_name }} ({{ $component->component_size }}) | Receivable: {{ number_format($component->receivable_quantity) }}
                          </option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="component_receivable_stock">Receivable Stock</label>
                    <input type="text" class="form-control" id="component_receivable_stock" name="component_receivable_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" step="1" class="form-control" name="r_quantityComponent" placeholder="0" id="r_quantityComponent">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnReceiveComponent" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>
              @endif

              {{-- Receive Items Table with Tabs --}}
              <div class="row">
                <div class="col-md-12">
                  <ul class="nav nav-tabs" id="receiveTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="receive-items-tab" data-toggle="tab" href="#receive-items" role="tab">Receive Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="issued-items-tab" data-toggle="tab" href="#issued-items" role="tab">Issued Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="received-items-tab" data-toggle="tab" href="#received-items" role="tab">Received Items</a>
                    </li>
                  </ul>
                  <div class="tab-content" id="receiveTabContent">
                    {{-- Receive Items Table --}}
                    <div class="tab-pane fade show active" id="receive-items" role="tabpanel">
                      <table class="table" id="receive-table">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Work/Cost</th>
                            <th>Quantity</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                    {{-- Issued Items Tab --}}
                    <div class="tab-pane fade" id="issued-items" role="tabpanel">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Type</th>
                            <th>Item / Product</th>
                            <th>Material / Stage / Component</th>
                            <th>Quantity</th>
                            <th>Average</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueItem->count())
                            @foreach($issueItem as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                @if($item->component_product_type_id)
                                  <td><span class="badge badge-warning">Component</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->component_article_no}} - {{$item->component_name}} ({{$item->component_size}})</td>
                                @elseif($item->material_id > 0)
                                  <td><span class="badge badge-info">Material</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->name}}</td>
                                @else
                                  <td><span class="badge badge-success">Product</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->stage}}</td>
                                @endif
                                <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                                <td>{{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0' }} Units</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                    {{-- Received Items Tab --}}
                    <div class="tab-pane fade" id="received-items" role="tabpanel">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Type</th>
                            <th>Article No</th>
                            <th>Material / Stage / Component</th>
                            <th>Quantity</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueSum->count())
                            @foreach($issueSum as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                @if($item->component_product_type_id)
                                  <td><span class="badge badge-warning">Component</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->component_article_no}} - {{$item->component_name}} ({{$item->component_size}})</td>
                                @elseif($item->material_id > 0)
                                  <td><span class="badge badge-info">Material</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->name}}</td>
                                @else
                                  <td><span class="badge badge-success">Product</span></td>
                                  <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                  <td>{{$item->stage}}</td>
                                @endif
                                <td>{{$item->total_quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <hr>

              <!-- Previous Receivings -->
              @if($receivings->count() > 0)
              <h5 class="text-info"><i class="fas fa-history"></i> Previous Receivings</h5>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Reference</th>
                      <th>Stage</th>
                      <th>Date</th>
                      <th>Received From</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($receivings as $receiving)
                      <tr>
                        <td>PTC-{{ $ptc->stock_no }} / {{ $receiving->stock_no }}</td>
                        <td>{{ $receiving->stage_name ?? 'N/A' }}</td>
                        <td>{{ $receiving->stock_date }}</td>
                        <td>{{ $receiving->employee_name ?? $receiving->vendor_name ?? '-' }}</td>
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
                  <button class="btn btn-success" type="submit" onclick="return submits()">
                    <i class="fas fa-arrow-circle-down"></i> Create Receiving
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
// PTC Receiving Page Variables
var isPtcReceivingPage = true;
var isPtcPage = true;

// Receive section data
var issueItems = @json($issueItem);
var rstock = {!! $rstock->toJson() !!};
var ajaxPCUrl = "{{ route('ajaxPC') }}";
var ajaxPSUrl = "{{ route('ajaxPS') }}";
var ptcProductTypeId = '{{ $product ? $product->product_type_id : 0 }}';
var ptcProductName = '{{ $product ? $product->name . " - " . $product->size_name : "N/A" }}';

// Component Product Type Receiving
$(document).ready(function() {
  // Update receivable stock when component is selected
  $('#r_component_product_type_id').on('change', function() {
    var selected = $(this).find(':selected');
    var receivable = selected.data('receivable') || 0;
    var unit = selected.data('unit') || 'Pcs';
    $('#component_receivable_stock').val(receivable + ' ' + unit);
    $('#r_quantityComponent').val('').attr('max', receivable);
  });

  // Add component to receive table
  $('#addBtnReceiveComponent').on('click', function() {
    var componentSelect = $('#r_component_product_type_id');
    var componentId = componentSelect.val();
    var quantity = parseFloat($('#r_quantityComponent').val()) || 0;

    if (!componentId || quantity <= 0) {
      alert('Please select a component and enter a valid quantity');
      return;
    }

    var selected = componentSelect.find(':selected');
    var receivable = parseFloat(selected.data('receivable')) || 0;
    var componentText = selected.text().split(' | ')[0]; // Get component name without receivable info
    var unit = selected.data('unit') || 'Pcs';

    if (quantity > receivable) {
      alert('Quantity cannot exceed receivable stock (' + receivable + ')');
      return;
    }

    // Add to receive table
    var rowCount = $('#receive-table tbody tr').length + 1;
    var row = '<tr>' +
      '<td>' + rowCount + '</td>' +
      '<td>' + componentText + ' <span class="badge badge-info">Component</span></td>' +
      '<td>-</td>' +
      '<td>-</td>' +
      '<td>' + quantity + ' ' + unit +
        '<input type="hidden" name="r_quantity[]" value="' + quantity + '">' +
        '<input type="hidden" name="r_product_type_id[]" value="0">' +
        '<input type="hidden" name="r_material_id[]" value="0">' +
        '<input type="hidden" name="r_stage_id[]" value="0">' +
        '<input type="hidden" name="r_work_logs[]" value="0">' +
        '<input type="hidden" name="r_component_product_type_id[]" value="' + componentId + '">' +
      '</td>' +
      '<td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>' +
      '</tr>';

    $('#receive-table tbody').append(row);

    // Update receivable quantity in dropdown
    var newReceivable = receivable - quantity;
    selected.data('receivable', newReceivable);
    if (newReceivable <= 0) {
      selected.remove();
    } else {
      selected.text(componentText + ' | Receivable: ' + newReceivable);
    }

    // Reset inputs
    componentSelect.val('').trigger('change');
    $('#component_receivable_stock').val('');
    $('#r_quantityComponent').val('');
  });
});
</script>
@endsection


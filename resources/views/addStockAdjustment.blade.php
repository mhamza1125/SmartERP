{{-- New stock adjustment (StockAdjustmentController::create / store) --}}
@extends('index')
@section('content')
@php
  $rejectedId = \App\Models\Stock::STAGE_REJECTED;
  $materialLabels = $materials->mapWithKeys(fn ($m) => [$m->material_id => $m->material_no.' - '.$m->name.($m->uname ? ' ('.$m->uname.')' : '')]);
  $productLabels = $productTypes->mapWithKeys(fn ($p) => [$p->product_type_id => $p->article_no.' - '.$p->name.' - Size '.$p->hname]);
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Stock Adjustment</h4>
            <div class="card-header-action">
              <a href="{{ route('stockAdjustment') }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stockAdjustment.store') }}" method="POST" id="adjustmentForm">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Adjustment No</label>
                    <input type="text" class="form-control" value="{{ $stockNo }}" readonly>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" name="stock_date" required value="{{ old('stock_date', date('Y-m-d')) }}">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Type</label>
                    <select class="form-control" name="adjustment_type" id="adjustment_type" required>
                      <option value="1" @selected(old('adjustment_type', '1') == '1')>Increase (+)</option>
                      <option value="2" @selected(old('adjustment_type') == '2')>Decrease (-)</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-2" id="orderField">
                  <div class="form-group">
                    <label title="A decrease takes this order's reserved stock first, then free stock">Use Stock Of Order</label>
                    <select class="form-control select2" name="order_id" style="width: 100%">
                      <option value="0">Free stock only</option>
                      @foreach($orders as $order)
                        <option value="{{ $order->order_id }}" @selected(old('order_id') == $order->order_id)>{{ $order->job_no }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md">{{-- fills the row (wider when Order is hidden) --}}
                  <div class="form-group">
                    <label>Reason</label>
                    <input type="text" class="form-control" name="description" required maxlength="2000" value="{{ old('description') }}" placeholder="e.g. Stock count difference, damaged in store">
                  </div>
                </div>
              </div>

              <h6>Items</h6>
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Item Type</label>
                    <select class="form-control" id="pick_type">
                      <option value="product">Product</option>
                      <option value="material">Material</option>
                    </select>
                  </div>
                </div>
                <div class="col-md js-pick-material d-none">
                  <div class="form-group">
                    <label>Material</label>
                    <select class="form-control select2" id="pick_material" style="width: 100%">
                      <option value="">Select Material</option>
                      @foreach($materialLabels as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md js-pick-product">
                  <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select2" id="pick_product" style="width: 100%">
                      <option value="">Select Product</option>
                      @foreach($productLabels as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-2 js-pick-product">
                  <div class="form-group">
                    <label>Stage</label>
                    <select class="form-control" id="pick_stage"></select>
                  </div>
                </div>
                <div class="col-md-2 js-pick-product js-pick-rejected d-none">
                  <div class="form-group">
                    <label>Rejected At</label>
                    <select class="form-control" id="pick_rejected"></select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" step="any" min="0" class="form-control" id="pick_qty" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="addLine">Add</button>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table" id="linesTable">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Item</th>
                      <th>Stage</th>
                      <th class="text-right">Quantity</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
              <p class="text-muted small">
                A decrease is refused when there is not enough stock. Decreasing Rejection stock takes only what was rejected at the chosen stage.
              </p>

              <div class="text-right">
                <button class="btn btn-primary" type="submit">Save Adjustment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  $(function () {
    var REJECTED = {{ $rejectedId }};
    var materials = @json($materialLabels);
    var products = @json($productLabels);
    var stagesByType = @json($stagesByType);
    var rejectionStock = @json($rejectionStock);
    var oldLines = @json(array_values(old('lines', [])));
    var index = 0;

    function stageName(pt, id) {
      if (+id === REJECTED) return 'Rejection';
      var stage = (stagesByType[pt] || []).find(function (s) { return s.id === +id; });
      return stage ? stage.name : '';
    }

    function toggleType() {
      var material = $('#pick_type').val() === 'material';
      $('.js-pick-material').toggleClass('d-none', !material);
      $('.js-pick-product').toggleClass('d-none', material);
      if (!material) toggleRejected();
    }

    function fillStages() {
      var pt = $('#pick_product').val();
      var $stage = $('#pick_stage').empty();
      var $rejected = $('#pick_rejected').empty();
      var stages = stagesByType[pt] || [];
      var stock = rejectionStock[pt] || {};
      stages.forEach(function (s) {
        $stage.append(new Option(s.name, s.id));
        $rejected.append(new Option(s.name + ' (' + (stock[s.id] || 0) + ' in stock)', s.id));
      });
      if (pt) $stage.append(new Option('Rejection', REJECTED));
      $rejected.append(new Option('Stage not recorded (' + (stock[0] || 0) + ' in stock)', 0));
      if (stages.length) $stage.val(stages[stages.length - 1].id);
      toggleRejected();
    }

    function toggleRejected() {
      $('.js-pick-rejected').toggleClass('d-none', $('#pick_type').val() === 'material' || +$('#pick_stage').val() !== REJECTED);
    }

    function hidden(name, value) {
      return $('<input type="hidden">').attr('name', 'lines[' + index + '][' + name + ']').val(value === null || value === undefined ? '' : value);
    }

    function addRow(line) {
      var isMaterial = line.type === 'material';
      var label = isMaterial ? materials[line.material_id] : products[line.product_type_id];
      if (!label || !(+line.quantity > 0)) return;
      var stage = isMaterial ? '-' : stageName(line.product_type_id, line.stage_id);
      if (!isMaterial && +line.stage_id === REJECTED) {
        stage += ' (rejected at ' + (+line.rejected_stage_id ? stageName(line.product_type_id, line.rejected_stage_id) : 'stage not recorded') + ')';
      }
      var $row = $('<tr>')
        .append($('<td class="js-sr">'))
        .append($('<td>').text(label).append(
          hidden('type', line.type), hidden('material_id', isMaterial ? line.material_id : ''),
          hidden('product_type_id', isMaterial ? '' : line.product_type_id), hidden('stage_id', isMaterial ? '' : line.stage_id),
          hidden('rejected_stage_id', !isMaterial && +line.stage_id === REJECTED ? line.rejected_stage_id : '')))
        .append($('<td>').text(stage))
        .append($('<td class="text-right">').text(line.quantity).append(hidden('quantity', line.quantity)))
        .append($('<td>').append($('<button type="button" class="btn btn-danger btn-sm js-remove">X</button>')));
      $('#linesTable tbody').append($row);
      index++;
      renumber();
    }

    function renumber() {
      $('#linesTable .js-sr').each(function (i) { $(this).text(i + 1); });
    }

    $('#pick_type').on('change', toggleType);
    $('#pick_product').on('change', fillStages);
    $('#pick_stage').on('change', toggleRejected);
    $('#adjustment_type').on('change', function () { $('#orderField').toggle($(this).val() === '2'); }).trigger('change');
    $(document).on('click', '.js-remove', function () { $(this).closest('tr').remove(); renumber(); });

    $('#addLine').on('click', function () {
      var type = $('#pick_type').val();
      var line = {
        type: type,
        material_id: $('#pick_material').val(),
        product_type_id: $('#pick_product').val(),
        stage_id: $('#pick_stage').val(),
        rejected_stage_id: $('#pick_rejected').val(),
        quantity: $('#pick_qty').val()
      };
      if ((type === 'material' && !line.material_id) || (type === 'product' && (!line.product_type_id || !line.stage_id)) || !(+line.quantity > 0)) {
        if (typeof swal !== 'undefined') swal('Missing details', 'Select the item, stage and a quantity above 0.', 'warning');
        return;
      }
      addRow(line);
      $('#pick_qty').val('');
    });

    $('#adjustmentForm').on('submit', function (e) {
      if (!$('#linesTable tbody tr').length) {
        e.preventDefault();
        if (typeof swal !== 'undefined') swal('No items', 'Add at least one item.', 'warning');
      }
    });

    toggleType();
    oldLines.forEach(addRow);
  });
</script>
@endpush

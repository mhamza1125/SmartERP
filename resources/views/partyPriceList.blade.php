{{--
  Prices of one vendor (buying: materials and products) or customer (selling: products) on the vendor /
  customer page (PartyPriceRepository::partySection). Set Price adds or changes one item's price; the
  purchase / order forms show it, and purchases / orders at another price update it.
  Vendors: the form lists only the vendor's own items (chosen on the vendor, main vendor of a material,
  or already priced). Saving an item that already has a price asks first, showing the last price.
  Params: $partyType, $partyId, $current, $changes, $materials, $productTypes, $stagesByType
--}}
@php
  $isVendor = $partyType === 'vendor';
  $policyModel = $isVendor ? App\Models\Purchase::class : App\Models\Order::class;
  $canSet = auth()->user()->can('create', $policyModel) || auth()->user()->can('edit', $policyModel);
  $money = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
  $formId = 'partyPriceListForm-'.$partyType;
  $hasItems = $materials->count() || $productTypes->count();
  // Current prices by item key (m-{material} | p-{size}-{stage}) for the "price already exists" check
  $priceMap = $current->mapWithKeys(fn ($row) => [
      ((int) $row->material_id > 0 ? 'm-'.$row->material_id : 'p-'.$row->product_type_id.'-'.$row->stage_id) => [
          'price' => $money($row->price),
          'raw' => (float) $row->price,
          'date' => formatDate($row->updated_at),
          'item' => $row->item_name.($row->item_type === 'product' ? ' - '.trim(($row->size_name ?? '').' / '.($row->stage_name ?? ''), ' /') : ''),
          'currency' => $row->currency_name ?? '',
      ],
  ]);
@endphp
<h5 class="mt-4">{{ $isVendor ? 'Buying Prices (this vendor)' : 'Selling Prices (this customer)' }}
  <span class="float-right">
    @if($current->count())
      <a href="{{ route('partyPrice.print', [$partyType, $partyId]) }}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Print Price List</a>
    @endif
    @if($canSet)
      <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#{{ $formId }}">Set Price</button>
    @endif
  </span>
</h5>
<p class="text-muted small mb-2">
  {{ $isVendor ? 'The price this vendor sells each material / product to us at.' : 'The price this customer buys each product at (customer currency).' }}
  It is shown on the {{ $isVendor ? 'purchase' : 'order' }} form and changes by itself when a {{ $isVendor ? 'purchase' : 'order' }} uses another price.
</p>

@if($canSet)
  <div class="collapse mb-3" id="{{ $formId }}">
    @if($isVendor && ! $hasItems)
      <div class="alert alert-light border mb-0">
        No materials or products are linked to this vendor yet.
        @can('edit', App\Models\Vendor::class)
          Add them on <a href="{{ route('vendor.edit', $partyId) }}">Edit Vendor</a> (Materials / Products), then set their prices here.
        @else
          They are chosen on the vendor's Edit page (Materials / Products).
        @endcan
      </div>
    @else
      <form action="{{ route('partyPrice.store') }}" method="POST" class="border rounded p-3 js-party-price-form">
        @csrf
        <input type="hidden" name="party_type" value="{{ $partyType }}">
        <input type="hidden" name="party_id" value="{{ $partyId }}">
        <div class="row">
          @if($isVendor)
            <div class="col-md-2">
              <div class="form-group">
                <label>Item Type</label>
                <select class="form-control js-pp-type">
                  @if($materials->count())<option value="material">Material</option>@endif
                  @if($productTypes->count())<option value="product">Product</option>@endif
                </select>
              </div>
            </div>
            <div class="col-md js-pp-material">
              <div class="form-group">
                <label>Material</label>
                <select class="form-control select2 js-pp-material-select" name="material_id" style="width: 100%">
                  <option value="">Select Material</option>
                  @foreach($materials as $material)
                    <option value="{{ $material->material_id }}">{{ $material->material_no }} - {{ $material->name }}{{ $material->uname ? ' ('.$material->uname.')' : '' }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          @endif
          <div class="col-md js-pp-product {{ $isVendor ? 'd-none' : '' }}">
            <div class="form-group">
              <label>Product</label>
              <select class="form-control select2 js-pp-product-select" name="product_type_id" style="width: 100%">
                <option value="">Select Product</option>
                @foreach($productTypes as $type)
                  <option value="{{ $type->product_type_id }}">{{ $type->article_no }} - {{ $type->name }} - Size {{ $type->hname }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2 js-pp-product {{ $isVendor ? 'd-none' : '' }}">
            <div class="form-group">
              <label>Stage</label>
              <select class="form-control js-pp-stage" name="stage_id"></select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.001" min="0.001" class="form-control js-pp-price" name="price" required placeholder="0">
              <small class="form-text text-muted js-pp-current"></small>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <label>&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-block">Save</button>
            </div>
          </div>
        </div>
        <small class="text-muted">
          @if($isVendor) Only this vendor's materials / products are listed (chosen on Edit Vendor). @endif
          If the item already has a price you are asked before it is replaced; the change is kept in the history below.
        </small>
      </form>
    @endif
  </div>
@endif

@if($current->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Item</th>
          <th>Size / Stage</th>
          <th class="text-right">Price</th>
          <th>Last Changed</th>
          @if($canSet)<th class="no-export">Action</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($current as $row)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->item_name }}</td>
            <td>{{ $row->item_type === 'material' ? ($row->unit_name ?? '-') : trim(($row->size_name ?? '').' / '.($row->stage_name ?? ''), ' /') }}</td>
            <td class="text-right">{{ $money($row->price) }} {{ $row->currency_name ?? '' }}</td>
            <td>{{ formatDate($row->updated_at) }}</td>
            @if($canSet)
              <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-primary js-pp-edit"
                  data-type="{{ $row->item_type }}" data-material="{{ $row->material_id }}"
                  data-product-type="{{ $row->product_type_id }}" data-stage="{{ $row->stage_id }}"
                  data-item="{{ $row->item_name }}{{ $row->item_type === 'product' ? ' - Size '.$row->size_name : '' }}"
                  data-stage-name="{{ $row->stage_name }}" data-price="{{ (float) $row->price }}">Edit</button>
                @include('deleteButton', ['url' => route('partyPrice.destroy', $row->party_price_id), 'check' => route('partyPrice.deleteCheck', $row->party_price_id), 'label' => 'Price of '.$row->item_name, 'text' => 'Remove'])
              </td>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <blockquote> No prices saved yet{{ $canSet ? ': use Set Price to enter them' : '' }} </blockquote>
@endif

<h6 class="mt-3">Price Change History
  @if(auth()->user()->can('access', $isVendor ? App\Models\Purchase::class : App\Models\Customer::class))
    <a href="{{ route('partyPrice.changes', [$partyType, 'party_id' => $partyId]) }}" class="small ml-2" target="_blank">All changes (more filters)</a>
  @endif
</h6>
@include('priceChangesTable', ['changes' => $changes, 'partyType' => $partyType, 'showParty' => false, 'filters' => true])

@if($canSet && (! $isVendor || $hasItems))
@push('scripts')
<script>
  $(function () {
    var stagesByType = @json($stagesByType);
    var prices = @json($priceMap);
    var $collapse = $('#{{ $formId }}');
    var $form = $collapse.find('form');
    var confirmed = false;

    function isMaterial() {
      var $type = $form.find('.js-pp-type');
      return $type.length ? $type.val() === 'material' : false;
    }
    function toggleType() {
      var material = isMaterial();
      $form.find('.js-pp-material').toggleClass('d-none', !material).find('select').prop('disabled', !material);
      $form.find('.js-pp-product').toggleClass('d-none', material).find('select').prop('disabled', material);
      showCurrent();
    }
    function fillStages(typeId, stageId) {
      var stages = stagesByType[typeId] || [];
      var $stage = $form.find('.js-pp-stage').empty();
      stages.forEach(function (s) { $stage.append(new Option(s.name, s.id)); });
      if (stages.length) $stage.val(stages[stages.length - 1].id); // default: the finished (last) stage
      if (stageId) $stage.val(String(stageId));
    }
    function itemKey() {
      if (isMaterial()) {
        var m = $form.find('.js-pp-material-select').val();
        return m ? 'm-' + m : null;
      }
      var t = $form.find('.js-pp-product-select').val(), s = $form.find('.js-pp-stage').val();
      return t && s ? 'p-' + t + '-' + s : null;
    }
    // Shows the saved price of the chosen item under the Price box
    function showCurrent() {
      var key = itemKey(), p = key ? prices[key] : null;
      $form.find('.js-pp-current').text(p ? 'Current: ' + p.price + (p.currency ? ' ' + p.currency : '') + ' (since ' + p.date + ')' : '');
    }
    // Adds an option the list does not have (an item priced before it was unlinked / deactivated)
    function ensureOption($select, value, text) {
      if (value && !$select.find('option[value="' + value + '"]').length) $select.append(new Option(text, value));
    }

    $form.find('.js-pp-type').on('change', toggleType);
    $form.find('.js-pp-product-select').on('change', function () { fillStages($(this).val()); showCurrent(); });
    $form.find('.js-pp-material-select, .js-pp-stage').on('change', showCurrent);

    // Edit on a price row: opens the form on that item with its price
    $(document).on('click', '.js-pp-edit', function () {
      var d = $(this).data();
      if (d.type === 'material') {
        $form.find('.js-pp-type').val('material');
        toggleType();
        ensureOption($form.find('.js-pp-material-select'), String(d.material), d.item);
        $form.find('.js-pp-material-select').val(String(d.material)).trigger('change');
      } else {
        $form.find('.js-pp-type').val('product');
        toggleType();
        var $product = $form.find('.js-pp-product-select');
        ensureOption($product, String(d.productType), d.item);
        $product.val(String(d.productType)).trigger('change.select2');
        fillStages(d.productType, d.stage);
        ensureOption($form.find('.js-pp-stage'), String(d.stage), d.stageName || 'Stage');
        $form.find('.js-pp-stage').val(String(d.stage));
        showCurrent();
      }
      $form.find('.js-pp-price').val(d.price);
      $collapse.collapse('show');
      $('html, body').animate({ scrollTop: $collapse.offset().top - 120 }, 200);
      $form.find('.js-pp-price').trigger('focus').trigger('select');
    });

    // Item already priced: show the last price and ask before replacing it
    $form.on('submit', function (e) {
      if (confirmed) return true;
      var key = itemKey(), p = key ? prices[key] : null;
      if (!p) return true;
      e.preventDefault();
      var newPrice = $form.find('.js-pp-price').val();
      var unit = p.currency ? ' ' + p.currency : '';
      if (Math.abs(parseFloat(newPrice) - p.raw) < 0.0005) {
        swal({ title: 'Price unchanged', text: p.item + ' already has the price ' + p.price + unit + ' (since ' + p.date + ').', icon: 'info', buttons: 'OK' });
        return false;
      }
      var box = document.createElement('div');
      box.innerHTML = '<div class="text-left">This item already has a price:<br><b>' + $('<div>').text(p.item).html() + '</b>'
        + '<table class="table table-sm mt-2 mb-0"><tr><td>Last price</td><td class="text-right"><b>' + p.price + unit + '</b> <small class="text-muted">since ' + p.date + '</small></td></tr>'
        + '<tr><td>New price</td><td class="text-right"><b>' + $('<div>').text(newPrice).html() + unit + '</b></td></tr></table>'
        + '<small class="text-muted">The last price is kept in the Price Change History.</small></div>';
      swal({ title: 'Update the existing price?', content: box, icon: 'warning', buttons: ['Cancel', 'Yes, update price'] })
        .then(function (ok) {
          if (ok) { confirmed = true; $form[0].submit(); }
        });
      return false;
    });

    if ($form.find('.js-pp-type').length) toggleType();
  });
</script>
@endpush
@endif

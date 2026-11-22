{{--
  Prices of one vendor (buying: materials and products) or customer (selling: products) on the vendor /
  customer page (PartyPriceRepository::partySection). Set Price adds or changes one item's price; the
  purchase / order forms show it, and purchases / orders at another price update it.
  Params: $partyType, $partyId, $current, $changes, $materials, $productTypes, $stagesByType
--}}
@php
  $isVendor = $partyType === 'vendor';
  $policyModel = $isVendor ? App\Models\Purchase::class : App\Models\Order::class;
  $canSet = auth()->user()->can('create', $policyModel) || auth()->user()->can('edit', $policyModel);
  $money = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
  $formId = 'partyPriceListForm-'.$partyType;
@endphp
<h5 class="mt-4">{{ $isVendor ? 'Buying Prices (this vendor)' : 'Selling Prices (this customer)' }}
  @if($canSet)
    <button type="button" class="btn btn-sm btn-primary float-right" data-toggle="collapse" data-target="#{{ $formId }}">Set Price</button>
  @endif
</h5>
<p class="text-muted small mb-2">
  {{ $isVendor ? 'The price this vendor sells each material / product to us at.' : 'The price this customer buys each product at (customer currency).' }}
  It is shown on the {{ $isVendor ? 'purchase' : 'order' }} form and changes by itself when a {{ $isVendor ? 'purchase' : 'order' }} uses another price.
</p>

@if($canSet)
  <div class="collapse mb-3" id="{{ $formId }}">
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
                <option value="material">Material</option>
                <option value="product">Product</option>
              </select>
            </div>
          </div>
          <div class="col-md js-pp-material">
            <div class="form-group">
              <label>Material</label>
              <select class="form-control select2" name="material_id" style="width: 100%">
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
            <input type="number" step="0.001" min="0.001" class="form-control" name="price" required placeholder="0">
          </div>
        </div>
        <div class="col-md-1">
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-block">Save</button>
          </div>
        </div>
      </div>
      <small class="text-muted">If this {{ $partyType }} already has a price for the item, it is replaced and the change is kept in the history below.</small>
    </form>
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
              <td>@include('deleteButton', ['url' => route('partyPrice.destroy', $row->party_price_id), 'check' => route('partyPrice.deleteCheck', $row->party_price_id), 'label' => 'Price of '.$row->item_name, 'text' => 'Remove'])</td>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <blockquote> No prices saved yet{{ $canSet ? ': use Set Price to enter them' : '' }} </blockquote>
@endif

<h6 class="mt-3">Price Change History</h6>
@include('priceChangesTable', ['changes' => $changes, 'partyType' => $partyType, 'showParty' => false])

@if($canSet)
@push('scripts')
<script>
  $(function () {
    var stagesByType = @json($stagesByType);
    var $form = $('#{{ $formId }} form');
    function toggleType() {
      var material = $form.find('.js-pp-type').val() === 'material';
      $form.find('.js-pp-material').toggleClass('d-none', !material).find('select').prop('disabled', !material);
      $form.find('.js-pp-product').toggleClass('d-none', material).find('select').prop('disabled', material);
    }
    $form.find('.js-pp-type').on('change', toggleType);
    $form.find('.js-pp-product-select').on('change', function () {
      var stages = stagesByType[$(this).val()] || [];
      var $stage = $form.find('.js-pp-stage').empty();
      stages.forEach(function (s) { $stage.append(new Option(s.name, s.id)); });
      if (stages.length) $stage.val(stages[stages.length - 1].id); // default: the finished (last) stage
    });
    if ($form.find('.js-pp-type').length) toggleType();
  });
</script>
@endpush
@endif

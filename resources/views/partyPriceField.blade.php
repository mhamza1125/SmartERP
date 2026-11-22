{{--
  Read-only "Vendor Price" / "Customer Price" field for the purchase / order item row: shows the
  selected party's current price of the selected item (partyPrice.lookup), with a link that copies it
  into the Price box.
  Params:
    $partyType     'vendor' | 'customer'
    $partySelect   selector of the vendor / customer select
    $itemSelect    selector of the material / product size select
    $itemParam     'material_id' | 'product_type_id'
    $stageSelect   selector of the stage select (products only), optional
    $priceInput    selector of the Price input (default input[name="price"])
    $col           column class (default col-md-2)
--}}
@php
  $fieldId = 'partyPrice-'.$partyType;
  $label = $partyType === 'vendor' ? 'Vendor Price' : 'Customer Price';
@endphp
<div class="{{ $col ?? 'col-md-2' }}">
  <div class="form-group">
    <label>{{ $label }}</label>
    <input type="text" class="form-control" id="{{ $fieldId }}" readonly placeholder="-" title="{{ $label }} of the selected item">
    <small class="form-text text-muted" id="{{ $fieldId }}-hint"></small>
  </div>
</div>

@push('scripts')
<script>
  $(function () {
    var cfg = {
      url: @json(route('partyPrice.lookup')),
      partyType: @json($partyType),
      party: @json($partySelect),
      item: @json($itemSelect),
      itemParam: @json($itemParam),
      stage: @json($stageSelect ?? null),
      price: @json($priceInput ?? 'input[name="price"]'),
      label: @json(strtolower($partyType)),
    };
    var $field = $('#{{ $fieldId }}');
    var $hint = $('#{{ $fieldId }}-hint');
    var request = null;

    function show(value, hint, usable) {
      $field.val(value);
      $hint.empty().text(hint || '');
      if (usable) {
        $hint.append(' ').append($('<a href="#">').text('Use').on('click', function (e) {
          e.preventDefault();
          $(cfg.price).val(usable).trigger('change').trigger('keyup');
        }));
      }
    }

    function refresh() {
      var partyId = $(cfg.party).val();
      var itemId = $(cfg.item).val();
      var stageId = cfg.stage ? $(cfg.stage).val() : null;
      if (!partyId || !itemId) {
        show('', 'Select the ' + cfg.label + ' and item');
        return;
      }
      if (cfg.stage && !stageId) {
        show('', '');
        return;
      }
      var data = { party_type: cfg.partyType, party_id: partyId };
      data[cfg.itemParam] = itemId;
      if (stageId) data.stage_id = stageId;
      if (request) request.abort();
      request = $.ajax({ url: cfg.url, type: 'GET', data: data, dataType: 'json' })
        .done(function (response) {
          var d = response.data || {};
          if (d.found) {
            show(d.price, 'Since ' + d.date, d.price);
          } else if (d.other_stages && d.other_stages.length) {
            show('', 'No price at this stage. ' + d.other_stages.map(function (o) { return (o.stage || '-') + ': ' + o.price; }).join(', '));
          } else {
            show('', 'No price yet');
          }
        })
        .fail(function (xhr) {
          if (xhr.statusText !== 'abort') show('', 'Price not available');
        });
    }

    $(document).on('change', [cfg.party, cfg.item, cfg.stage].filter(Boolean).join(', '), refresh);
    // Product stages are filled by AJAX (ajaxPS) and selected without a change event
    $(document).ajaxComplete(function (event, xhr, settings) {
      if (cfg.stage && settings.url && settings.url.indexOf('ajaxPS') !== -1) refresh();
    });
    refresh();
  });
</script>
@endpush

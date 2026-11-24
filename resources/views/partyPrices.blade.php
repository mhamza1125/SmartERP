{{--
  Vendor (buying) or customer (selling) prices of one material / product, with their change history
  (PartyPriceRepository::currentFor / historyFor).
  Params:
    $partyType  'vendor' | 'customer'
    $itemType   'material' | 'product'
    $itemId     material_id / product_id
    $current    current prices
    $history    price changes, newest first
    $parties    vendors / customers for the Set Price form
    $sizes      product sizes (product only; product_type_id + name)
    $stages     product stages (product only; head_id + name)
  The caller shows this only to users allowed to see it (purchases access / customers access).
--}}
@php
  $isVendor = $partyType === 'vendor';
  $isProduct = $itemType === 'product';
  $partyLabel = $isVendor ? 'Vendor' : 'Customer';
  $policyModel = $isVendor ? App\Models\Purchase::class : App\Models\Order::class;
  $canSet = auth()->user()->can('create', $policyModel) || auth()->user()->can('edit', $policyModel);
  $money = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
  $formId = 'partyPriceForm-'.$partyType;
  $canOpenDoc = auth()->user()->can('show', $isVendor ? App\Models\Purchase::class : App\Models\Order::class);
  $docLink = function ($row) use ($isVendor, $canOpenDoc) {
      if (! $canOpenDoc || ! $row->doc_id || ! $row->doc_no) {
          return null;
      }
      return $isVendor ? route('purchase.show', $row->doc_id) : route('order.show', $row->doc_id);
  };
  $historyId = 'partyPriceHistory-'.$partyType.'-'.$itemType;
  // Current prices by "party|size-stage" (material: "party|m") for the "price already exists" check
  $priceMap = $current->mapWithKeys(fn ($row) => [
      $row->party_id.'|'.($isProduct ? $row->product_type_id.'-'.$row->stage_id : 'm') => [
          'price' => $money($row->price),
          'raw' => (float) $row->price,
          'date' => formatDate($row->updated_at),
          'label' => $row->party_no.($row->party_name ? ' - '.$row->party_name : '').($isProduct ? ', size '.$row->size_name.' / '.($row->stage_name ?? '') : ''),
          'currency' => $row->currency_name ?? '',
      ],
  ]);
  $sourceLabels = [
      'manual' => ['Set by hand', 'warning'],
      'purchase' => ['Purchase', 'success'],
      'order' => ['Order', 'success'],
      'backfill' => ['Imported', 'secondary'],
      'removed' => ['Removed', 'danger'],
  ];
@endphp

<h5 class="mt-3">{{ $partyLabel }} Prices ({{ $isVendor ? 'Buying' : 'Selling' }})
  @if($canSet)
    <button type="button" class="btn btn-sm btn-primary float-right" data-toggle="collapse" data-target="#{{ $formId }}">Set {{ $partyLabel }} Price</button>
  @endif
</h5>
<p class="text-muted small mb-2">
  The price each {{ strtolower($partyLabel) }} {{ $isVendor ? 'sells to us' : 'buys' }} at. It changes by itself when a {{ $isVendor ? 'purchase' : 'order' }} uses another price (higher or lower).
  @if(! $isVendor) Prices are in the customer's currency. @endif
</p>

@if($canSet)
  <div class="collapse mb-3" id="{{ $formId }}">
    <form action="{{ route('partyPrice.store') }}" method="POST" class="border rounded p-3 js-item-price-form">
      @csrf
      <input type="hidden" name="party_type" value="{{ $partyType }}">
      @if(! $isProduct)
        <input type="hidden" name="material_id" value="{{ $itemId }}">
      @endif
      <div class="row">
        <div class="col-md">{{-- vendor / customer: takes the free width --}}
          <div class="form-group">
            <label>{{ $partyLabel }}</label>
            <select class="form-control select2 js-ip-party" name="party_id" required style="width: 100%">
              <option value="" selected disabled>Select {{ $partyLabel }}</option>
              @foreach($parties as $party)
                @if($isVendor)
                  <option value="{{ $party->vendor_id }}">{{ $party->vendor_no }} - {{ $party->fname }}</option>
                @else
                  <option value="{{ $party->customer_id }}">{{ $party->customer_no }} - {{ $party->fname }} {{ $party->lname }}{{ $party->currency_name ? ' ('.$party->currency_name.')' : '' }}</option>
                @endif
              @endforeach
            </select>
          </div>
        </div>
        @if($isProduct)
          <div class="col-md-3">
            <div class="form-group">
              <label>Size</label>
              <select class="form-control select2 js-ip-size" name="product_type_id" required style="width: 100%">
                <option value="" selected disabled>Select Size</option>
                @foreach($sizes as $size)
                  <option value="{{ $size->product_type_id }}">{{ $size->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Stage</label>
              <select class="form-control select2 js-ip-stage" name="stage_id" required style="width: 100%">
                @foreach($stages as $stageItem)
                  <option value="{{ $stageItem->head_id }}" {{ $loop->last ? 'selected' : '' }}>{{ $stageItem->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endif
        <div class="col-md-2">
          <div class="form-group">
            <label>Price</label>
            <input type="number" step="0.001" min="0.001" class="form-control js-ip-price" name="price" required placeholder="0">
            <small class="form-text text-muted js-ip-current"></small>
          </div>
        </div>
        <div class="col-md-1">
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-block">Save</button>
          </div>
        </div>
      </div>
      <small class="text-muted">If the {{ strtolower($partyLabel) }} already has a price for this item you are asked before it is replaced; the change is kept in the history.</small>
    </form>
  </div>
@endif

@if($current->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>{{ $partyLabel }}</th>
          @if($isProduct)<th>Size</th><th>Stage</th>@endif
          <th class="text-right">Price</th>
          <th>Last Changed</th>
          <th>Changed By</th>
          @if($canSet)<th class="no-export">Action</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($current as $row)
          @php [$label, $class] = $sourceLabels[$row->source] ?? [ucfirst($row->source), 'light']; $link = $docLink($row); @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->party_no }}{{ $row->party_name ? ' - '.$row->party_name : '' }}</td>
            @if($isProduct)<td>{{ $row->size_name }}</td><td>{{ $row->stage_name ?? '-' }}</td>@endif
            <td class="text-right">{{ $money($row->price) }} {{ $row->currency_name ?? '' }}</td>
            <td>{{ formatDate($row->updated_at) }}</td>
            <td>
              <span class="badge badge-{{ $class }}">{{ $label }}</span>
              @if($link)<a href="{{ $link }}" target="_blank">{{ $row->doc_no }}</a> @else {{ $row->doc_no }} @endif
            </td>
            @if($canSet)
              <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-primary js-ip-edit" data-form="{{ $formId }}" data-party="{{ $row->party_id }}"
                  data-size="{{ $row->product_type_id }}" data-stage="{{ $row->stage_id }}" data-price="{{ (float) $row->price }}">Edit</button>
                @include('deleteButton', ['url' => route('partyPrice.destroy', $row->party_price_id), 'check' => route('partyPrice.deleteCheck', $row->party_price_id), 'label' => $partyLabel.' price of '.$row->party_no, 'text' => 'Remove'])
              </td>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <blockquote> No {{ strtolower($partyLabel) }} prices yet </blockquote>
@endif

<h6 class="mt-3">{{ $partyLabel }} Price History</h6>
@if($history->count())
  @if($history->count() > 1)
    <div class="form-row align-items-center mb-2 js-table-filter" data-target="#{{ $historyId }}">
      <div class="col-md-4 mb-1"><select class="form-control form-control-sm" data-col="2" data-label="All {{ $partyLabel }}s"></select></div>
      @if($isProduct)
        <div class="col-md-2 mb-1"><select class="form-control form-control-sm" data-col="3" data-label="All Sizes"></select></div>
      @endif
      <div class="col-md-2 mb-1"><select class="form-control form-control-sm" data-col="{{ $isProduct ? 9 : 7 }}" data-label="All Users"></select></div>
      <div class="col-md-auto mb-1">
        <button type="button" class="btn btn-sm btn-outline-secondary js-filter-reset">Clear</button>
        <small class="text-muted ml-2 js-filter-count"></small>
      </div>
    </div>
  @endif
  <div class="table-responsive">
    <table class="table table-sm table-striped" id="{{ $historyId }}">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Date</th>
          <th>{{ $partyLabel }}</th>
          @if($isProduct)<th>Size</th><th>Stage</th>@endif
          <th class="text-right">Old Price</th>
          <th class="text-right">New Price</th>
          <th class="text-right">Change</th>
          <th>Changed By</th>
          <th>User</th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $row)
          @php
            [$label, $class] = $sourceLabels[$row->source] ?? [ucfirst($row->source), 'light'];
            $link = $docLink($row);
            $diff = ($row->old_price !== null && $row->price !== null) ? (float) $row->price - (float) $row->old_price : null;
            $pct = ($diff !== null && (float) $row->old_price > 0) ? $diff / (float) $row->old_price * 100 : null;
          @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatDate($row->created_at) }}</td>
            <td>{{ $row->party_no }}{{ $row->party_name ? ' - '.$row->party_name : '' }}</td>
            @if($isProduct)<td>{{ $row->size_name }}</td><td>{{ $row->stage_name ?? '-' }}</td>@endif
            <td class="text-right">{{ $row->old_price === null ? 'New' : $money($row->old_price) }}</td>
            <td class="text-right">{{ $row->price === null ? 'Removed' : $money($row->price) }}</td>
            <td class="text-right">
              @if($diff !== null)
                {{-- a rise costs us when buying, earns us when selling --}}
                <span class="{{ ($diff > 0) === $isVendor ? 'text-danger' : 'text-success' }}">{{ $diff > 0 ? '+' : '' }}{{ $money($diff) }}{{ $pct !== null ? ' ('.($diff > 0 ? '+' : '').number_format($pct, 1).'%)' : '' }}</span>
              @else
                -
              @endif
            </td>
            <td>
              <span class="badge badge-{{ $class }}">{{ $label }}</span>
              @if($link)<a href="{{ $link }}" target="_blank">{{ $row->doc_no }}</a> @else {{ $row->doc_no }} @endif
            </td>
            <td>{{ $row->user_name ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <blockquote> No price changes yet </blockquote>
@endif

@if($canSet)
@push('scripts')
<script>
  $(function () {
    var prices = @json($priceMap);
    var isProduct = {{ $isProduct ? 'true' : 'false' }};
    var $collapse = $('#{{ $formId }}');
    var $form = $collapse.find('form');
    var confirmed = false;

    function key() {
      var party = $form.find('.js-ip-party').val();
      if (!party) return null;
      if (!isProduct) return party + '|m';
      var size = $form.find('.js-ip-size').val(), stage = $form.find('.js-ip-stage').val();
      return size && stage ? party + '|' + size + '-' + stage : null;
    }
    function showCurrent() {
      var k = key(), p = k ? prices[k] : null;
      $form.find('.js-ip-current').text(p ? 'Current: ' + p.price + (p.currency ? ' ' + p.currency : '') + ' (since ' + p.date + ')' : '');
    }
    $form.find('.js-ip-party, .js-ip-size, .js-ip-stage').on('change', showCurrent);

    // Edit on a price row: opens the form on that vendor / size / stage with its price
    $(document).on('click', '.js-ip-edit[data-form="{{ $formId }}"]', function () {
      var d = $(this).data();
      $form.find('.js-ip-party').val(String(d.party)).trigger('change');
      if (isProduct) {
        $form.find('.js-ip-size').val(String(d.size)).trigger('change');
        $form.find('.js-ip-stage').val(String(d.stage)).trigger('change');
      }
      $form.find('.js-ip-price').val(d.price);
      $collapse.collapse('show');
      $('html, body').animate({ scrollTop: $collapse.offset().top - 120 }, 200);
      $form.find('.js-ip-price').trigger('focus').trigger('select');
    });

    // Price already saved for this vendor / item: show the last price and ask before replacing it
    $form.on('submit', function (e) {
      if (confirmed) return true;
      var k = key(), p = k ? prices[k] : null;
      if (!p) return true;
      e.preventDefault();
      var newPrice = $form.find('.js-ip-price').val();
      var unit = p.currency ? ' ' + p.currency : '';
      if (Math.abs(parseFloat(newPrice) - p.raw) < 0.0005) {
        swal({ title: 'Price unchanged', text: p.label + ' already has the price ' + p.price + unit + ' (since ' + p.date + ').', icon: 'info', buttons: 'OK' });
        return false;
      }
      var box = document.createElement('div');
      box.innerHTML = '<div class="text-left">A price already exists for:<br><b>' + $('<div>').text(p.label).html() + '</b>'
        + '<table class="table table-sm mt-2 mb-0"><tr><td>Last price</td><td class="text-right"><b>' + p.price + unit + '</b> <small class="text-muted">since ' + p.date + '</small></td></tr>'
        + '<tr><td>New price</td><td class="text-right"><b>' + $('<div>').text(newPrice).html() + unit + '</b></td></tr></table>'
        + '<small class="text-muted">The last price is kept in the price history.</small></div>';
      swal({ title: 'Update the existing price?', content: box, icon: 'warning', buttons: ['Cancel', 'Yes, update price'] })
        .then(function (ok) {
          if (ok) { confirmed = true; $form[0].submit(); }
        });
      return false;
    });
  });
</script>
@endpush
@endif

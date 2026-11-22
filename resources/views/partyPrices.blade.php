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
    <form action="{{ route('partyPrice.store') }}" method="POST" class="border rounded p-3">
      @csrf
      <input type="hidden" name="party_type" value="{{ $partyType }}">
      @if(! $isProduct)
        <input type="hidden" name="material_id" value="{{ $itemId }}">
      @endif
      <div class="row">
        <div class="col-md">{{-- vendor / customer: takes the free width --}}
          <div class="form-group">
            <label>{{ $partyLabel }}</label>
            <select class="form-control select2" name="party_id" required style="width: 100%">
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
              <select class="form-control select2" name="product_type_id" required style="width: 100%">
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
              <select class="form-control select2" name="stage_id" required style="width: 100%">
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
      <small class="text-muted">If the {{ strtolower($partyLabel) }} already has a price for this item, it is replaced and the change is kept in the history.</small>
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
              <td>@include('deleteButton', ['url' => route('partyPrice.destroy', $row->party_price_id), 'check' => route('partyPrice.deleteCheck', $row->party_price_id), 'label' => $partyLabel.' price of '.$row->party_no, 'text' => 'Remove'])</td>
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
  <div class="table-responsive">
    <table class="table table-sm table-striped">
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

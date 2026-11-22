{{--
  Vendor / customer price changes (PartyPriceRepository::changes), newest first.
  Params: $changes, $partyType ('vendor' | 'customer'), $showParty (default true), $empty (text when none)
--}}
@php
  $isVendor = $partyType === 'vendor';
  $showParty = $showParty ?? true;
  $money = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
  $canOpenDoc = auth()->user()->can('show', $isVendor ? App\Models\Purchase::class : App\Models\Order::class);
  $canOpenItem = [
      'material' => auth()->user()->can('show', App\Models\Material::class),
      'product' => auth()->user()->can('show', App\Models\Product::class),
  ];
  $sourceLabels = [
      'manual' => ['Set by hand', 'warning'],
      'purchase' => ['Purchase', 'success'],
      'order' => ['Order', 'success'],
      'backfill' => ['Imported', 'secondary'],
      'removed' => ['Removed', 'danger'],
  ];
@endphp
@if($changes->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead>
        <tr>
          <th>Date</th>
          @if($showParty)<th>{{ $isVendor ? 'Vendor' : 'Customer' }}</th>@endif
          <th>Item</th>
          <th>Size / Stage</th>
          <th class="text-right">Old Price</th>
          <th class="text-right">New Price</th>
          <th class="text-right">Change</th>
          <th>Changed By</th>
          <th>User</th>
        </tr>
      </thead>
      <tbody>
        @foreach($changes as $row)
          @php
            [$label, $class] = $sourceLabels[$row->source] ?? [ucfirst($row->source), 'light'];
            $diff = ($row->old_price !== null && $row->price !== null) ? (float) $row->price - (float) $row->old_price : null;
            $pct = ($diff !== null && (float) $row->old_price > 0) ? $diff / (float) $row->old_price * 100 : null;
            $docRoute = $isVendor ? 'purchase.show' : 'order.show';
            $itemRoute = $row->item_type === 'material' ? 'material.show' : 'product.show';
          @endphp
          <tr>
            <td class="text-nowrap">{{ formatDate($row->created_at) }}</td>
            @if($showParty)<td>{{ $row->party_no }}{{ $row->party_name ? ' - '.$row->party_name : '' }}</td>@endif
            <td>
              @if($row->item_id && $canOpenItem[$row->item_type])
                <a href="{{ route($itemRoute, $row->item_id) }}" target="_blank">{{ $row->item_name }}</a>
              @else
                {{ $row->item_name ?? '-' }}
              @endif
            </td>
            <td>{{ $row->item_type === 'material' ? ($row->unit_name ?? '-') : trim(($row->size_name ?? '').' / '.($row->stage_name ?? ''), ' /') }}</td>
            <td class="text-right">{{ $row->old_price === null ? 'New' : $money($row->old_price) }}</td>
            <td class="text-right">{{ $row->price === null ? 'Removed' : $money($row->price) }} {{ $row->currency_name ?? '' }}</td>
            <td class="text-right text-nowrap">
              @if($diff !== null)
                {{-- a rise costs us when buying, earns us when selling --}}
                <span class="{{ ($diff > 0) === $isVendor ? 'text-danger' : 'text-success' }}">{{ $diff > 0 ? '+' : '' }}{{ $money($diff) }}{{ $pct !== null ? ' ('.($diff > 0 ? '+' : '').number_format($pct, 1).'%)' : '' }}</span>
              @else
                -
              @endif
            </td>
            <td class="text-nowrap">
              <span class="badge badge-{{ $class }}">{{ $label }}</span>
              @if($row->doc_id && $row->doc_no)
                @if($canOpenDoc)<a href="{{ route($docRoute, $row->doc_id) }}" target="_blank">{{ $row->doc_no }}</a>@else {{ $row->doc_no }} @endif
              @endif
            </td>
            <td>{{ $row->user_name ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <p class="text-muted mb-0">{{ $empty ?? 'No price changes yet.' }}</p>
@endif

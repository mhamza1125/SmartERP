{{--
  Vendor price comparison of one material / product (PurchaseItemRepository::vendorComparison).
  $comparison: rows per vendor, cheapest last price first. $unit: optional unit name for quantities.
--}}
@php
  $unit = $unit ?? '';
  $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
  $cheapest = $comparison->min('last_price');
@endphp
@if($comparison->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Vendor</th>
          <th class="text-right">Purchases</th>
          <th class="text-right">QTY</th>
          <th class="text-right">Received</th>
          <th class="text-right">Lowest Price</th>
          <th class="text-right">Highest Price</th>
          <th class="text-right">Average Price</th>
          <th class="text-right">Last Price</th>
          <th>Last Purchase</th>
        </tr>
      </thead>
      <tbody>
        @foreach($comparison as $vendor)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              @if($vendor->vendor_id && auth()->user()->can('show', App\Models\Vendor::class))
                <a href="{{ route('vendor.show', $vendor->vendor_id) }}" target="_blank">{{ $vendor->vendor_no }}{{ $vendor->fname ? ' - '.$vendor->fname : '' }}</a>
              @else
                {{ $vendor->vendor_no }}{{ $vendor->fname ? ' - '.$vendor->fname : '' }}
              @endif
            </td>
            <td class="text-right">{{ $vendor->purchases }}</td>
            <td class="text-right">{{ $qty($vendor->quantity) }} {{ $unit }}</td>
            <td class="text-right">{{ $qty($vendor->received_qty) }} {{ $unit }}</td>
            <td class="text-right">{{ number_format($vendor->lowest_price, 2) }}</td>
            <td class="text-right">{{ number_format($vendor->highest_price, 2) }}</td>
            <td class="text-right">{{ number_format($vendor->average_price, 2) }}</td>
            <td class="text-right">
              {{ number_format($vendor->last_price, 2) }}
              @if($comparison->count() > 1 && $vendor->last_price == $cheapest)<span class="badge badge-success">Lowest</span>@endif
            </td>
            <td>{{ formatDate($vendor->last_date) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <small class="text-muted d-block mb-3">Average price is weighted by quantity. Material processing charges are not included in the comparison.</small>
@else
  <blockquote> No vendor purchases </blockquote>
@endif

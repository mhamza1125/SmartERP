{{--
  Product wages of one employee / contractor (ProductCostRepository::personWages / wageHistory):
  their own wage per product and work next to the general wage, then their wage change history.
  Params: $personWages, $personWageHistory.
--}}
@php
  $num = fn ($v) => $v === null ? null : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
@endphp
<h5 class="mt-4 mb-2">Product Wages (Own Rates)</h5>
<p class="text-muted small mb-2">Wages set for this person on the products' Costing / Wages. Work without an own rate is paid at the general wage.</p>
@if($personWages->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Product</th>
          <th>Work / Cost Head</th>
          <th class="text-right">Own Wage</th>
          <th class="text-right">General Wage</th>
          <th class="text-right">Difference</th>
          <th>Last Changed</th>
        </tr>
      </thead>
      <tbody>
        @foreach($personWages as $row)
          @php $diff = $row->general_amount !== null ? (float) $row->amount - (float) $row->general_amount : null; @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              @can('show', App\Models\Product::class)
                <a href="{{ route('product.show', $row->product_id) }}" target="_blank">{{ $row->article_no }}</a>
              @else
                {{ $row->article_no }}
              @endcan
              {{ $row->product_name ? ' - '.$row->product_name : '' }}
            </td>
            <td>{{ $row->head_name ?? '-' }}</td>
            <td class="text-right font-weight-bold">{{ $num($row->amount) }}</td>
            <td class="text-right">{{ $num($row->general_amount) ?? 'Not set' }}</td>
            <td class="text-right">
              @if($diff === null || abs($diff) < 0.005)
                -
              @else
                <span class="badge badge-{{ $diff > 0 ? 'warning' : 'info' }}">{{ $diff > 0 ? '+' : '' }}{{ $num($diff) }}</span>
              @endif
            </td>
            <td>{{ formatDate($row->last_changed_at ?? $row->updated_at) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <blockquote> No own wage rates; paid at the general wage </blockquote>
@endif

<h5 class="mt-3 mb-2">Wage Change History</h5>
@include('wageHistory', ['history' => $personWageHistory, 'showPerson' => false, 'showProduct' => true])

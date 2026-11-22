{{--
  Wage change history (ProductCostRepository::wageHistory), newest first.
  Params: $history; $showPerson (column "Wage Of"), $showProduct (column "Product").
--}}
@php
  $showPerson = $showPerson ?? false;
  $showProduct = $showProduct ?? true;
  $num = fn ($v) => $v === null ? null : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
  $actionLabels = [
      'added' => ['Added', 'success'],
      'changed' => ['Changed', 'warning'],
      'removed' => ['Removed', 'danger'],
      'opening' => ['Existing', 'secondary'],
  ];
@endphp
@if($history->count())
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>Sr.</th>
          <th>Date</th>
          @if($showProduct)<th>Product</th>@endif
          <th>Work / Cost Head</th>
          @if($showPerson)<th>Wage Of</th>@endif
          <th class="text-right">Old Wage</th>
          <th class="text-right">New Wage</th>
          <th class="text-right">Change</th>
          <th>Action</th>
          <th>User</th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $row)
          @php
            [$label, $class] = $actionLabels[$row->action] ?? [ucfirst($row->action), 'light'];
            $diff = ($row->old_amount !== null && $row->amount !== null) ? (float) $row->amount - (float) $row->old_amount : null;
            $pct = ($diff !== null && (float) $row->old_amount > 0) ? $diff / (float) $row->old_amount * 100 : null;
          @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ formatDate($row->created_at) }}</td>
            @if($showProduct)
              <td>
                @can('show', App\Models\Product::class)
                  <a href="{{ route('product.show', $row->product_id) }}" target="_blank">{{ $row->article_no }}</a>
                @else
                  {{ $row->article_no }}
                @endcan
                {{ $row->product_name ? ' - '.$row->product_name : '' }}
              </td>
            @endif
            <td>{{ $row->head_name ?? '-' }}</td>
            @if($showPerson)<td>{{ $row->person_name }}</td>@endif
            <td class="text-right">{{ $num($row->old_amount) ?? '-' }}</td>
            <td class="text-right">{{ $num($row->amount) ?? 'Removed' }}</td>
            <td class="text-right">
              @if($diff !== null)
                {{ $diff > 0 ? '+' : '' }}{{ $num($diff) }}{{ $pct !== null ? ' ('.($diff > 0 ? '+' : '').number_format($pct, 1).'%)' : '' }}
              @else
                -
              @endif
            </td>
            <td><span class="badge badge-{{ $class }}">{{ $label }}</span></td>
            <td>{{ $row->user_name ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <small class="text-muted d-block mb-3">"Existing" = the wage as it was when wage history started (its last save date).</small>
@else
  <blockquote> No wage changes recorded yet </blockquote>
@endif

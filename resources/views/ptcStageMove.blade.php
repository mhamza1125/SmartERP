{{-- PTC stage movement: [from] -> [to] badges. Expects $move from PtcStockRepository::movementStages() --}}
@php
  $move = $move ?? ['from' => [], 'to' => []];
  $kindBadge = ['stage' => 'badge-primary', 'rejected' => 'badge-danger', 'general' => 'badge-dark', 'none' => 'badge-light border'];
@endphp
<div class="d-flex align-items-center text-nowrap">
  @foreach(['from', 'to'] as $side)
    <div>
      @foreach(collect($move[$side])->unique(fn ($point) => implode('|', $point)) as $point)
        <div class="d-inline-block text-center align-top mr-1">
          <span class="badge {{ $kindBadge[$point['kind']] ?? 'badge-secondary' }}">{{ $point['label'] }}</span>
          @if($point['note'])
            <div class="small text-muted">{{ $point['note'] }}</div>
          @endif
        </div>
      @endforeach
    </div>
    @if($side === 'from')
      <i class="fas fa-long-arrow-alt-right text-muted mx-2"></i>
    @endif
  @endforeach
</div>
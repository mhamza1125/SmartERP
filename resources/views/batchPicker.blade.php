{{-- Batch quantities for one product/stage row of a stock-out form.
     Expects $item with product_type_id, stage_id and batches (batch_id, batch_no, order_id, job_no, balance);
     optional $picks ("{pt}_{stage}" => [batch_id => qty]) to pre-fill an edit form.
     Quantities typed here are taken from those batches first; the rest is taken automatically
     (the order's own batches first, then free stock, oldest first). --}}
@php
  $batchOptions = collect($item->batches ?? [])->map(fn ($batch) => (object) $batch);
  $pinKey = $item->product_type_id . '_' . $item->stage_id;
  $prefill = ($picks ?? [])[$pinKey] ?? [];
  $values = $batchOptions->mapWithKeys(fn ($batch) => [$batch->batch_id => old('batch_qty.' . $pinKey . '.' . $batch->batch_id, $prefill[$batch->batch_id] ?? '')]);
  $collapseId = 'batchQty' . $pinKey . '_' . uniqid();
@endphp
@if($batchOptions->isNotEmpty())
  <div class="batch-qty mt-1" data-key="{{ $pinKey }}">
    <a class="small" data-toggle="collapse" href="#{{ $collapseId }}" role="button">
      <i class="fas fa-boxes"></i> Batches: <span class="batch-qty-summary">Auto</span>
    </a>
    <div class="collapse {{ $values->filter()->isNotEmpty() ? 'show' : '' }}" id="{{ $collapseId }}">
      <table class="table table-sm table-borderless mb-0">
        @foreach($batchOptions as $batch)
          <tr>
            <td class="small align-middle py-0">
              {{ $batch->batch_no }}
              @if($batch->order_id)
                <span class="badge badge-warning">{{ $batch->job_no ?? 'Reserved' }}</span>
              @else
                <span class="badge badge-success">Free</span>
              @endif
            </td>
            <td class="small align-middle text-right py-0 text-nowrap">{{ number_format($batch->balance) }} left</td>
            <td class="py-0" style="width: 90px">
              <input type="number" step="any" min="0" max="{{ $batch->balance }}" class="form-control form-control-sm batch-qty-input"
                name="batch_qty[{{ $pinKey }}][{{ $batch->batch_id }}]" value="{{ $values[$batch->batch_id] }}" placeholder="0">
            </td>
          </tr>
        @endforeach
      </table>
      <small class="text-muted">Empty = Auto: this order's batches first, then free stock, oldest first.</small>
    </div>
  </div>
@endif

@once
  @push('scripts')
    <script>
      // "Batches: Auto" / "30 picked + rest Auto" next to each batch quantity list
      $(function () {
        function refreshBatchSummary($box) {
          var picked = 0;
          $box.find('.batch-qty-input').each(function () {
            picked += parseFloat($(this).val()) || 0;
          });
          $box.find('.batch-qty-summary').text(picked > 0 ? picked + ' picked + rest Auto' : 'Auto');
        }
        $(document).on('input change', '.batch-qty-input', function () {
          refreshBatchSummary($(this).closest('.batch-qty'));
        });
        // Removing a product row puts its stage back on Auto, so leftover batch quantities
        // cannot add up to more than is issued
        $(document).on('click', '.deletepRow', function () {
          var $row = $(this).closest('tr');
          var key = $row.find('input[name="product_type_id[]"]').val() + '_' + $row.find('input[name="stage_id[]"]').val();
          var $box = $('.batch-qty[data-key="' + key + '"]');
          $box.find('.batch-qty-input').val('');
          refreshBatchSummary($box);
        });
        $('.batch-qty').each(function () {
          refreshBatchSummary($(this));
        });
      });
    </script>
  @endpush
@endonce

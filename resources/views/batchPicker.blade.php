{{-- Batch picker for one product/stage row of a stock-out form.
     Expects $item with product_type_id, stage_id and batches (batch_id, batch_no, order_id, job_no, balance).
     "Auto" takes the order's own batches first, then free stock, oldest first. --}}
@php
  $batchOptions = collect($item->batches ?? [])->map(fn ($batch) => (object) $batch);
  $pinKey = $item->product_type_id . '_' . $item->stage_id;
@endphp
@if($batchOptions->isNotEmpty())
  <select class="form-control form-control-sm mt-1" name="batch_pin[{{ $pinKey }}]" title="Batch to take this product from">
    <option value="">Batch: Auto (order first, then free)</option>
    @foreach($batchOptions as $batch)
      <option value="{{ $batch->batch_id }}" @selected(old('batch_pin.' . $pinKey) == $batch->batch_id)>
        {{ $batch->batch_no }} &middot; {{ $batch->order_id ? 'Reserved ' . ($batch->job_no ?? '') : 'Free' }} &middot; {{ number_format($batch->balance) }}
      </option>
    @endforeach
  </select>
@endif

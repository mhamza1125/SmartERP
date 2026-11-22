{{--
  "Return From" cell of a purchase return line (add / edit return). A purchased product can be returned
  from any of its stages after work was done on it, or from Rejection with the stage it was rejected at;
  the vendor is still credited the purchase price. Material lines post empty values (no stage).
  Params: $item (receive line), $isMaterial, $lineProductType, $productStages, $rejectionStock,
          $selectedStage (NULL = purchased stage), $selectedRejected
--}}
@if($isMaterial)
  <input type="hidden" name="stage_id[]" value="">
  <input type="hidden" name="rejected_stage_id[]" value="">
  -
@else
  @php
    $pt = $lineProductType[$item->receive_material_id] ?? null;
    $stages = $productStages[$pt] ?? collect();
    $purchasedStage = (int) ($item->product_stage_id ?? 0);
    $current = (int) ($selectedStage ?? 0) ?: $purchasedStage;
    $rejected = \App\Models\Stock::STAGE_REJECTED;
    $buckets = $rejectionStock[$pt] ?? [];
  @endphp
  <select class="form-control form-control-sm js-return-from" name="stage_id[]" title="Stage the returned pieces are taken from">
    @foreach($stages as $stage)
      <option value="{{ $stage->head_id }}" @selected($current === (int) $stage->head_id)>{{ $stage->name }}{{ (int) $stage->head_id === $purchasedStage ? ' (as purchased)' : '' }}</option>
    @endforeach
    <option value="{{ $rejected }}" @selected($current === $rejected)>Rejection</option>
  </select>
  <select class="form-control form-control-sm mt-1 js-rejected-at {{ $current === $rejected ? '' : 'd-none' }}" name="rejected_stage_id[]" title="Stage the pieces were rejected at">
    @foreach($stages as $stage)
      <option value="{{ $stage->head_id }}" @selected((int) ($selectedRejected ?? -1) === (int) $stage->head_id)>Rejected at {{ $stage->name }} ({{ number_format((float) ($buckets[$stage->head_id] ?? 0)) }} in stock)</option>
    @endforeach
    <option value="0" @selected($current === $rejected && (int) ($selectedRejected ?? -1) === 0)>Stage not recorded ({{ number_format((float) ($buckets[0] ?? 0)) }} in stock)</option>
  </select>
@endif

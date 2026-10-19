{{-- Optional batch quantities per stage for the product issued on a PTC form.
     Expects $batchChoices from StockBatchRepository::batchChoices(). --}}
@if(isset($batchChoices) && $batchChoices->isNotEmpty())
  <div class="row">
    <div class="col-md-12">
      <label class="mb-0">Take Product From Batches</label>
      <small class="text-muted d-block mb-1">
        Optional. Enter how many pieces to take from each batch (e.g. 30 from one, 20 from another).
        Whatever is not entered is taken automatically: this order's batches first, then free stock, oldest first.
      </small>
    </div>
    @foreach($batchChoices as $choice)
      <div class="form-group col-md-4">
        <small class="text-muted">{{ $choice->stage_name }}</small>
        @include('batchPicker', ['item' => $choice])
      </div>
    @endforeach
  </div>
@endif

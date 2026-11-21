{{--
  Asset / liability entry form (addAsset / editAsset).
  Expects $register, $types (TYPES of the register), $names (existing names), $banks (company
  accounts), $entry (null when adding) and $presetName.
--}}
@php
  $moneyOf = collect($types)->map(fn ($type) => $type['money'])->all();
  $selectedType = old('transaction_type', $entry?->transaction_type ?? '');
  $amount = old('amount', $entry ? (float) ($entry->debit ?? $entry->credit) : '');
  $affects = (bool) old('affects_money', $entry ? $entry->transaction_id !== null : false);
  $selectedBank = (string) old('bank_id', $entry?->bank_id ?? '0');
@endphp
<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ $register['title'] }} Name <span class="text-danger">*</span></label>
  <div class="col-sm-12 col-md-7">
    <input type="text" class="form-control @error('asset_name') is-invalid @enderror" name="asset_name" list="registerNames" autocomplete="off"
           value="{{ old('asset_name', $entry?->asset_name ?? $presetName) }}" required maxlength="255">
    <datalist id="registerNames">
      @foreach($names as $name)<option value="{{ $name }}">@endforeach
    </datalist>
    <small class="form-text text-muted">{{ $register['nameHint'] }}</small>
    @error('asset_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>
<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Entry Type <span class="text-danger">*</span></label>
  <div class="col-sm-12 col-md-7">
    <select class="form-control @error('transaction_type') is-invalid @enderror" name="transaction_type" id="entryType" required>
      <option value="">Select Entry Type</option>
      <optgroup label="Increase">
        @foreach($types as $key => $type)
          @if($type['sign'] > 0)<option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>@endif
        @endforeach
      </optgroup>
      <optgroup label="Decrease">
        @foreach($types as $key => $type)
          @if($type['sign'] < 0)<option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>@endif
        @endforeach
      </optgroup>
    </select>
    @error('transaction_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>
<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Date <span class="text-danger">*</span></label>
  <div class="col-sm-12 col-md-7">
    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" name="transaction_date"
           value="{{ old('transaction_date', $entry?->transaction_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    @error('transaction_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>
<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Amount <span class="text-danger">*</span></label>
  <div class="col-sm-12 col-md-7">
    <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ $amount }}" required>
    @error('amount')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>

{{-- Cash / bank: only for entry types that can move money --}}
<div class="form-group row mb-2" id="moneyRow">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Cash / Bank</label>
  <div class="col-sm-12 col-md-7">
    <div class="custom-control custom-checkbox mt-2">
      <input type="hidden" name="affects_money" value="0">
      <input type="checkbox" class="custom-control-input" id="affectsMoney" name="affects_money" value="1" {{ $affects ? 'checked' : '' }}>
      <label class="custom-control-label" for="affectsMoney">Affects cash / bank: <span id="moneyDirection"></span></label>
    </div>
    <small class="form-text text-muted">Ticked: the amount is posted to the selected account's Cash Book / Bank Ledger. Unticked: only the {{ strtolower($register['title']) }} changes (e.g. an opening value, or paid through another entry).</small>
    @error('affects_money')<span class="text-danger small d-block">{{ $message }}</span>@enderror
  </div>
</div>
<div class="form-group row mb-4" id="bankRow">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Account <span class="text-danger">*</span></label>
  <div class="col-sm-12 col-md-7">
    <select class="form-control @error('bank_id') is-invalid @enderror" name="bank_id" id="bankId">
      <option value="0" {{ $selectedBank === '0' ? 'selected' : '' }}>Cash</option>
      @foreach($banks as $bank)
        <option value="{{ $bank->bank_id }}" {{ $selectedBank === (string) $bank->bank_id ? 'selected' : '' }}>{{ $bank->hname }} - {{ $bank->account_title }} ({{ $bank->account }})</option>
      @endforeach
    </select>
    @error('bank_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>

<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Description</label>
  <div class="col-sm-12 col-md-7">
    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" maxlength="2000">{{ old('description', $entry?->description) }}</textarea>
    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>
<div class="form-group row mb-4">
  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Attachment</label>
  <div class="col-sm-12 col-md-7">
    @if($entry?->attachment)
      <div class="mb-2"><a href="{{ asset('storage/' . $entry->attachment) }}" target="_blank">Current attachment</a> <small class="text-muted">(a new file replaces it)</small></div>
    @endif
    <input type="file" class="form-control @error('attachment') is-invalid @enderror" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
    <small class="form-text text-muted">PDF, JPG, JPEG, PNG (max 2 MB)</small>
    @error('attachment')<span class="invalid-feedback">{{ $message }}</span>@enderror
  </div>
</div>

@push('scripts')
<script>
  (function () {
    var money = @json($moneyOf);
    var type = document.getElementById('entryType');
    var affects = document.getElementById('affectsMoney');
    var moneyRow = document.getElementById('moneyRow');
    var bankRow = document.getElementById('bankRow');
    var direction = document.getElementById('moneyDirection');

    function refresh() {
      var way = money[type.value] || null;
      moneyRow.style.display = way ? '' : 'none';
      if (!way) affects.checked = false;
      direction.textContent = way === 'in' ? 'money is received into the account' : (way === 'out' ? 'money is paid from the account' : '');
      bankRow.style.display = way && affects.checked ? '' : 'none';
    }

    type.addEventListener('change', refresh);
    affects.addEventListener('change', refresh);
    refresh();
  })();
</script>
@endpush

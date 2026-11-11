{{--
    <option>s for picking one of the company's bank accounts on an invoice print modal.
    $banks comes from BankRepository::self() (hname = bank name from heads).
--}}
<option value="">-- No Bank Details --</option>
@foreach($banks ?? [] as $bank)
  @php
    $bankLabel = collect([
        $bank->hname,
        $bank->account_title,
        $bank->account ? 'A/C ' . $bank->account : null,
        $bank->iban ? 'IBAN ' . $bank->iban : null,
        $bank->branch_code ? 'Branch ' . $bank->branch_code : null,
    ])->filter(fn ($part) => trim((string) $part) !== '')->implode(' | ');
  @endphp
  <option value="{{ $bank->bank_id }}">{{ $bankLabel }}</option>
@endforeach

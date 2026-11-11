@extends('print.layout-voucher')

{{--
    Half-page (top half of a portrait A4 sheet) voucher for payments, receipts, expenses, balance adjustments and bank/cash transfers.
    $info = TransactionRepository::describe(): type, party, accounts, purchase / order, cheque no, note.
--}}
@php
    $type = $transaction['transaction_type'] ?? '';
    $isTransfer = $type === 'transfer';
    $isAdjustment = ($transaction['transaction_to'] ?? '') === 'brs';
    $isReceipt = in_array($type, ['orderPayment', 'receiveAdvance'], true);
    $debit = (float) ($transaction['debit'] ?? 0);
    $credit = (float) ($transaction['credit'] ?? 0);
    $amount = abs($debit != 0 ? $debit : $credit);
    $details = $info->details ?? [];
    $note = $details['Description'] ?? null;
    unset($details['Description']);

    $isExpense = ($transaction['transaction_to'] ?? '') === 'expense';
    $docTitle = $isTransfer ? 'Transfer Voucher' : ($isAdjustment ? 'Adjustment Voucher' : ($isReceipt ? 'Receipt Voucher' : ($isExpense ? 'Expense Voucher' : 'Payment Voucher')));

    // Double entry lines: [account, debit, credit]
    if ($isTransfer) {
        $lines = [
            [$details['To Account'] ?? '-', $amount, null],
            [$details['From Account'] ?? '-', null, $amount],
        ];
    } elseif ($isAdjustment) {
        $lines = [[$details['Account'] ?? 'Cash', $debit > 0 ? $amount : null, $debit > 0 ? null : $amount]];
    } else {
        $partyLine = trim(($info->party ?? '') !== '' ? $info->party : ($info->type ?? 'Payment'));
        foreach (['Purchase No' => 'Purchase', 'Order No' => 'Order', 'Delivery No' => 'Delivery'] as $key => $label) {
            if (!empty($details[$key])) {
                $partyLine .= ' (' . $label . ' ' . $details[$key] . ')';
            }
        }
        $accountLine = $details['Paid From'] ?? $details['Received Into'] ?? 'Cash';
        $lines = [
            [$partyLine, $credit ?: null, $debit ?: null],
            [$accountLine, $debit ?: null, $credit ?: null],
        ];
    }
    $totalDebit = collect($lines)->sum(fn ($line) => (float) $line[1]);
    $totalCredit = collect($lines)->sum(fn ($line) => (float) $line[2]);
@endphp

@section('title', str_replace(' ', '_', $docTitle) . '_' . ($voucherNumber ?? 'N/A') . '_' . formatDate($transaction['transaction_date'] ?? now()))
@section('doc_title', $docTitle)
@section('doc_no', $voucherNumber ?? '')

@push('styles')
<style>
    .voucher-note { font-size: 9.5px; margin: 2px 0 6px; padding: 3px 6px; border: 1px solid var(--hairline); background: var(--row-alt); }
    .voucher-note b { color: var(--muted); font-weight: 600; margin-right: 4px; }
</style>
@endpush

@section('content')
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">Voucher No</span>
        <span class="meta-value voucher-number">{{ $voucherNumber ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Date</span>
        <span class="meta-value">{{ formatDate($transaction['transaction_date'] ?? null, 'N/A') }}</span>
    </div>
    @foreach($details as $label => $value)
        <div class="meta-field">
            <span class="meta-label">{{ $label }}</span>
            <span class="meta-value">{{ $value }}</span>
        </div>
    @endforeach
</div>

@if($note)
    <div class="voucher-note"><b>Description:</b>{{ $note }}</div>
@endif

<table class="print-table">
    <thead>
        <tr>
            <th class="text-left">Account</th>
            <th class="num" style="width: 22%">Debit</th>
            <th class="num" style="width: 22%">Credit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lines as [$account, $lineDebit, $lineCredit])
            <tr>
                <td class="text-left">{{ $account }}</td>
                <td class="num">{{ $lineDebit ? number_format($lineDebit, 2) : '-' }}</td>
                <td class="num">{{ $lineCredit ? number_format($lineCredit, 2) : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="text-right">Total</td>
            <td class="num">{{ number_format($totalDebit, 2) }}</td>
            <td class="num">{{ number_format($totalCredit, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>{{ $isReceipt ? 'Amount Received' : ($isTransfer ? 'Amount Transferred' : ($isAdjustment ? 'Adjustment Amount' : 'Amount Paid')) }}</span>
        <span class="amount">{{ number_format($amount, 2) }}</span>
    </div>
</div>

@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <span class="amount-words-label">Amount in Words:</span>
    <span>{{ numberToWordsWithCurrency($amount) }}</span>
</div>
@endif

<div class="signatures avoid-break">
    @foreach(['Prepared By', 'Verified By', 'Approved By', $isReceipt ? 'Paid By' : 'Received By'] as $signature)
        <div class="signature-box">
            <div class="signature-line-sm"></div>
            <div class="signature-label">{{ $signature }}</div>
        </div>
    @endforeach
</div>
@endsection

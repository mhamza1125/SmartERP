@extends('print.layout-voucher')

{{--
    Half-page voucher of one asset / liability entry (AssetEntryController::printEntry), same sheet
    as the payment vouchers. Payment Voucher when the entry paid money out, Receipt Voucher when it
    brought money in, Journal Voucher when no cash / bank moved.
    Double entry: asset increase = Dr asset / Cr counter account; asset decrease = Dr counter / Cr asset;
    liability increase = Dr counter / Cr liability; liability decrease = Dr liability / Cr counter.
    Counter account = the cash / bank account when money moved, else the entry type (e.g. Depreciation).
--}}
@php
    $debit = (float) ($entry->debit ?? 0);
    $credit = (float) ($entry->credit ?? 0);
    $amount = $debit > 0 ? $debit : $credit;
    $increase = $debit > 0;
    $moneyMoved = (bool) $entry->transaction_id;
    $money = $type['money'] ?? null;
    $docTitle = ! $moneyMoved ? 'Journal Voucher' : ($money === 'in' ? 'Receipt Voucher' : 'Payment Voucher');
    $voucherNumber = strtoupper(substr($register['kind'], 0, 3)).'-'.str_pad($entry->asset_id, 4, '0', STR_PAD_LEFT);
    $account = $moneyMoved
        ? ($entry->bank_id ? trim(($entry->bank_name ?? 'Bank').' - '.$entry->account_title, ' -') : 'Cash')
        : ($type['short'] ?? ucfirst($entry->transaction_type));
    $itemLine = $register['title'].': '.$entry->asset_name;
    // Asset side is debited when it grows; a liability is credited when it grows
    $itemDebited = $register['kind'] === 'asset' ? $increase : ! $increase;
    $lines = $itemDebited
        ? [[$itemLine, $amount, null], [$account, null, $amount]]
        : [[$account, $amount, null], [$itemLine, null, $amount]];
@endphp

@section('title', str_replace(' ', '_', $docTitle).'_'.$voucherNumber.'_'.formatDate($entry->transaction_date))
@section('doc_title', $docTitle)

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
        <span class="meta-value voucher-number">{{ $voucherNumber }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Date</span>
        <span class="meta-value">{{ formatDate($entry->transaction_date, 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $register['title'] }}</span>
        <span class="meta-value">{{ $entry->asset_name }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Entry Type</span>
        <span class="meta-value">{{ $type['label'] ?? ucfirst($entry->transaction_type) }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $money === 'in' ? 'Received Into' : 'Paid From' }}</span>
        <span class="meta-value">{{ $moneyMoved ? $account : 'No cash / bank movement' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $register['balance'] }} After</span>
        <span class="meta-value">{{ number_format($balanceAfter, 2) }}</span>
    </div>
</div>

@if($entry->description)
    <div class="voucher-note"><b>Description:</b>{{ $entry->description }}</div>
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
        @foreach($lines as [$lineAccount, $lineDebit, $lineCredit])
            <tr>
                <td class="text-left">{{ $lineAccount }}</td>
                <td class="num">{{ $lineDebit ? number_format($lineDebit, 2) : '-' }}</td>
                <td class="num">{{ $lineCredit ? number_format($lineCredit, 2) : '-' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="text-right">Total</td>
            <td class="num">{{ number_format($amount, 2) }}</td>
            <td class="num">{{ number_format($amount, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>{{ ! $moneyMoved ? 'Voucher Amount' : ($money === 'in' ? 'Amount Received' : 'Amount Paid') }}</span>
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
    @foreach(['Prepared By', 'Verified By', 'Approved By', $money === 'in' ? 'Paid By' : 'Received By'] as $signature)
        <div class="signature-box">
            <div class="signature-line-sm"></div>
            <div class="signature-label">{{ $signature }}</div>
        </div>
    @endforeach
</div>
@endsection

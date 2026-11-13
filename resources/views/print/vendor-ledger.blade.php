@extends('print.layout')

@section('title', 'Vendor Ledger - ' . ($vendor['fname'] ?? 'N/A'))
@section('doc_title', 'Vendor Ledger')
@section('doc_no', $vendor['vendor_no'] ?? '')

@section('content')
{{-- Vendor Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Vendor No</span>
            <span class="meta-value">{{ $vendor['vendor_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Vendor Name</span>
            <span class="meta-value">{{ $vendor['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Address</span>
            <span class="meta-value">{{ $vendor['address'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $vendor['phone1'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Report Period</span>
            <span class="meta-value">{{ $dfrom ?? 'All Time' }} to {{ $dto ?? 'Current' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Opening Balance</span>
            <span class="meta-value amount">{{ number_format($oBalance ?? 0, 2) }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Vendor Type</span>
            <span class="meta-value">{{ $vendor['vendor_type'] == '1' ? 'Contractor' : 'Vendor' }}</span>
        </div>
    </div>
</div>

{{-- Ledger Transactions Table --}}
@if(isset($detail) && count($detail) > 0)
<div class="avoid-break">
    <h3>Transaction History</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 13%">Reference</th>
                <th style="width: 41%">Description</th>
                <th class="num" style="width: 12%">Debit</th>
                <th class="num" style="width: 12%">Credit</th>
                <th class="num" style="width: 12%">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $oBalance ?? 0;
            @endphp

            @if($oBalance != 0)
            <tr style="background-color: #f9f9f9;">
                <td>{{ $dfrom ?? 'Start' }}</td>
                <td>-</td>
                <td><strong>Opening Balance</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right amount"><strong>{{ number_format($runningBalance, 2) }}</strong></td>
            </tr>
            @endif

            @foreach($detail as $rowKey => $transaction)
                @php
                    $debit = $transaction->debit ?? 0;
                    $credit = $transaction->credit ?? 0;

                    // For vendor/contractor ledger (liability account):
                    // DB debit (displayed as Credit) = purchases/work done, increases liability = ADD to balance
                    // DB credit (displayed as Debit) = payments made, decreases liability = SUBTRACT from balance
                    // Include ALL transaction types - no filtering
                    $runningBalance += $debit - $credit;

                    // For vendor/contractor ledger, reverse the display (DB debit shown in credit column, DB credit shown in debit column)
                    $displayDebit = $credit;
                    $displayCredit = $debit;
                @endphp
                <tr>
                    @php $vlDate = $transaction->transaction_date ?? $transaction->timestamp ?? null; @endphp
                    <td>{{ $vlDate ? \Carbon\Carbon::parse($vlDate)->format('d-m-Y') : 'N/A' }}</td>
                    <td>
                        @if(isset($transaction->transaction_id))
                            TXN-{{ date('Y') }}-{{ str_pad($transaction->transaction_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif(isset($transaction->purchase_no))
                            {{ $transaction->purchase_no }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $notes[$rowKey] ?? strip_tags($transaction->description ?? $transaction->purchase_no ?? 'Transaction') }}</td>
                    @php $sumDebit = ($sumDebit ?? 0) + $displayDebit; $sumCredit = ($sumCredit ?? 0) + $displayCredit; @endphp
                    <td class="text-right amount">{{ $displayDebit > 0 ? number_format($displayDebit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ $displayCredit > 0 ? number_format($displayCredit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="3" class="text-right">Total / Closing Balance:</td>
                <td class="text-right amount">{{ number_format($sumDebit ?? 0, 2) }}</td>
                <td class="text-right amount">{{ number_format($sumCredit ?? 0, 2) }}</td>
                <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection

@extends('print.layout')

@section('title', 'Vendor Ledger - ' . ($vendor['fname'] ?? 'N/A'))

@section('content')
<div class="document-title">Vendor Ledger</div>

{{-- Vendor Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor No:</span>
            <span class="info-value">{{ $vendor['vendor_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor Name:</span>
            <span class="info-value">{{ $vendor['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $vendor['address'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $vendor['phone1'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Report Period:</span>
            <span class="info-value">{{ $dfrom ?? 'All Time' }} to {{ $dto ?? 'Current' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Opening Balance:</span>
            <span class="info-value amount">{{ number_format($oBalance ?? 0, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor Type:</span>
            <span class="info-value">{{ $vendor['vendor_type'] == '1' ? 'Contractor' : 'Vendor' }}</span>
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
                <th style="width: 12%">Date</th>
                <th style="width: 15%">Reference</th>
                <th style="width: 35%">Description</th>
                <th style="width: 12%">Debit</th>
                <th style="width: 12%">Credit</th>
                <th style="width: 14%">Balance</th>
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

            @foreach($detail as $transaction)
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
                    <td>{{ strip_tags($transaction->description ?? $transaction->purchase_no ?? 'Transaction') }}</td>
                    <td class="text-right amount">{{ $displayDebit > 0 ? number_format($displayDebit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ $displayCredit > 0 ? number_format($displayCredit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="3" class="text-right">Closing Balance:</td>
                <td class="text-right amount">{{ number_format($detail->sum('debit') ?? 0, 2) }}</td>
                <td class="text-right amount">{{ number_format($detail->sum('credit') ?? 0, 2) }}</td>
                <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Summary Section --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Purchases:</span>
        <span class="amount">{{ number_format($detail->sum('debit') ?? 0, 2) }}</span>
    </div>
    <div class="total-row">
        <span>Total Payments:</span>
        <span class="amount">{{ number_format($detail->sum('credit') ?? 0, 2) }}</span>
    </div>
    <div class="total-row grand-total">
        <span>Current Balance:</span>
        <span class="amount">{{ number_format($runningBalance ?? 0, 2) }}</span>
    </div>
</div>

{{-- Balance Status --}}
<div class="info-section avoid-break">
    <h3>Account Status</h3>
    <div style="padding: 10px; border: 1px solid #333; background-color: {{ $runningBalance > 0 ? '#d4edda' : ($runningBalance < 0 ? '#f8d7da' : '#f9f9f9') }};">
        @if($runningBalance > 0)
            <strong>Credit Balance:</strong> Company owes vendor {{ number_format(abs($runningBalance), 2) }}
        @elseif($runningBalance < 0)
            <strong>Debit Balance:</strong> Vendor owes company {{ number_format(abs($runningBalance), 2) }}
        @else
            <strong>Balanced Account:</strong> No outstanding amount
        @endif
    </div>
</div>

@endsection

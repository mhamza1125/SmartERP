@extends('print.layout')

@section('title', 'Customer_Ledger_' . ($customer['customer_no'] ?? 'N/A') . '_' . ($dfrom ?? 'All') . '_to_' . ($dto ?? 'Current'))

@section('content')
<div class="document-title">Customer Ledger</div>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer No:</span>
            <span class="info-value">{{ $customer['customer_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ ($customer['fname'] ?? '') . ' ' . ($customer['lname'] ?? '') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $customer['email'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $customer['phone'] ?? 'N/A' }}</span>
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
            <span class="info-label">Country:</span>
            <span class="info-value">{{ $customer['coname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Currency:</span>
            <span class="info-value">{{ $customer['cuname'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Address Information --}}
@if(isset($customer['address']) && !empty($customer['address']))
<div class="info-section avoid-break">
    <h3>Address</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $customer['address'] }}
    </div>
</div>
@endif

{{-- Ledger Transactions Table --}}
@if(isset($detail) && $detail->count() > 0)
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

                    // For customer ledger:
                    // - Deliveries (stored in debit) should display in Debit column
                    // - Payments (stored in debit) should display in Credit column
                    $isPayment = isset($transaction->transaction_type) && $transaction->transaction_type == 'orderPayment';

                    if ($isPayment) {
                        // Payment: stored in debit, but display in credit column
                        $displayDebit = 0;
                        $displayCredit = $debit;
                        $runningBalance += $debit; // Payment increases balance (reduces receivable)
                    } else {
                        // Delivery: stored in debit, display in debit column
                        $displayDebit = $debit;
                        $displayCredit = $credit;
                        $runningBalance += $credit - $debit;
                    }
                @endphp
                <tr>
                    <td>{{ $transaction->stock_date ?? $transaction->transaction_date ?? 'N/A' }}</td>
                    <td>
                        @if(isset($transaction->transaction_id))
                            TXN-{{ date('Y') }}-{{ str_pad($transaction->transaction_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif(isset($transaction->stock_no))
                            {{ $transaction->stock_no }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(isset($transaction->transaction_type))
                            {{ ucfirst($transaction->transaction_type) }}
                        @elseif(isset($transaction->stock_no))
                            Delivery: {{ $transaction->stock_no }}
                        @else
                            Transaction
                        @endif
                    </td>
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
                <td class="text-right amount">{{ number_format($balance ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Summary Section --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Sales:</span>
        <span class="amount">{{ number_format($detail->sum('debit') ?? 0, 2) }}</span>
    </div>
    <div class="total-row">
        <span>Total Payments:</span>
        <span class="amount">{{ number_format($detail->sum('credit') ?? 0, 2) }}</span>
    </div>
    <div class="total-row grand-total">
        <span>Current Balance:</span>
        <span class="amount">{{ number_format($balance ?? 0, 2) }}</span>
    </div>
</div>

{{-- Balance Status --}}
<div class="info-section avoid-break">
    <h3>Account Status</h3>
    <div style="padding: 10px; border: 1px solid #333; background-color: {{ $balance > 0 ? '#d4edda' : ($balance < 0 ? '#f8d7da' : '#f9f9f9') }};">
        @if($balance > 0)
            <strong>Credit Balance:</strong> Customer owes company {{ number_format(abs($balance), 2) }}
        @elseif($balance < 0)
            <strong>Debit Balance:</strong> Company owes customer {{ number_format(abs($balance), 2) }}
        @else
            <strong>Balanced Account:</strong> No outstanding amount
        @endif
    </div>
</div>

@endsection

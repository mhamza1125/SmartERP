@extends('print.layout')

@section('title', 'Employee_Ledger_' . ($employee['employee_no'] ?? 'N/A') . '_' . ($dfrom ?? 'All') . '_to_' . ($dto ?? 'Current'))
@section('doc_title', 'Employee Ledger')
@section('doc_no', $employee['employee_no'] ?? '')

@section('content')
{{-- Employee Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Employee No</span>
            <span class="meta-value">{{ $employee['employee_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Employee Name</span>
            <span class="meta-value">{{ $employee['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Department</span>
            <span class="meta-value">{{ $employee['dname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Employee Type</span>
            <span class="meta-value">{{ $employee['etname'] ?? 'N/A' }}</span>
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
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $employee['phone'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">CNIC</span>
            <span class="meta-value">{{ $employee['cnic'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Ledger Transactions Table --}}
@if(isset($detail) && $detail->count() > 0)
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

                    // For employee ledger (liability account):
                    // DB debit (displayed as Credit) = work done, increases liability = ADD to balance
                    // DB credit (displayed as Debit) = payments made, decreases liability = SUBTRACT from balance
                    // Include ALL transaction types - no filtering
                    $runningBalance += $debit - $credit;

                    // For employee ledger, reverse the display (DB debit shown in credit column, DB credit shown in debit column)
                    $displayDebit = $credit;
                    $displayCredit = $debit;
                @endphp
                <tr>
                    <td>{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : 'N/A' }}</td>
                    <td>
                        @if(isset($transaction->transaction_id))
                            TXN-{{ date('Y') }}-{{ str_pad($transaction->transaction_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif(isset($transaction->stock_no))
                            {{ $transaction->stock_no }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $notes[$rowKey] ?? strip_tags($transaction->description ?? 'Transaction') }}</td>
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
        <span>Total Wages Earned:</span>
        <span class="amount">{{ number_format($detail->sum('debit') ?? 0, 2) }}</span>
    </div>
    <div class="total-row">
        <span>Total Payments Received:</span>
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
            <strong>Credit Balance:</strong> Company owes employee {{ number_format(abs($balance), 2) }}
        @elseif($balance < 0)
            <strong>Debit Balance:</strong> Employee owes company {{ number_format(abs($balance), 2) }}
        @else
            <strong>Balanced Account:</strong> No outstanding amount
        @endif
    </div>
</div>

@endsection

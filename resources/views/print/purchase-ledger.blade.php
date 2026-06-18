@extends('print.layout')

@section('title', 'Purchase_Ledger_' . ($dfrom ?? 'All') . '_to_' . ($dto ?? 'Current'))

@section('content')
<div class="document-title">Purchase Ledger</div>

{{-- Report Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Report Type:</span>
            <span class="info-value">Purchase Ledger</span>
        </div>
        <div class="info-row">
            <span class="info-label">Report Period:</span>
            <span class="info-value">{{ $dfrom ?? 'All Time' }} to {{ $dto ?? 'Current' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Opening Balance:</span>
            <span class="info-value amount">{{ number_format($oBalance ?? 0, 2) }}</span>
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
                <th style="width: 8%">Sr.</th>
                <th style="width: 12%">Date</th>
                <th style="width: 15%">Reference</th>
                <th style="width: 35%">Description</th>
                <th style="width: 10%">Debit</th>
                <th style="width: 10%">Credit</th>
                <th style="width: 10%">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $index = 1;
                $runningBalance = $oBalance ?? 0;
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp

            @if($oBalance != 0)
            <tr style="background-color: #f9f9f9;">
                <td>{{ $index++ }}</td>
                <td>{{ $dfrom ?? 'Start' }}</td>
                <td>-</td>
                <td><strong>Opening Balance</strong></td>
                <td class="text-right">{{ $oBalance > 0 ? number_format($oBalance, 2) : '-' }}</td>
                <td class="text-right">{{ $oBalance < 0 ? number_format(abs($oBalance), 2) : '-' }}</td>
                <td class="text-right amount"><strong>{{ number_format($runningBalance, 2) }}</strong></td>
            </tr>
            @endif

            @foreach($detail as $transaction)
                @php
                    $debit = $transaction->debit ?? 0;
                    $credit = $transaction->credit ?? 0;
                    $totalDebit += $debit;
                    $totalCredit += $credit;
                    $runningBalance += $debit - $credit;

                    // Determine reference number and description
                    $refNo = '-';
                    $description = 'Transaction';

                    if(isset($transaction->purchase_no)) {
                      $refNo = $transaction->purchase_no;
                      $description = 'Purchase';
                    } elseif(isset($transaction->return_no)) {
                      $refNo = $transaction->return_no;
                      $description = 'Return';
                    } elseif(isset($transaction->transaction_id)) {
                      $refNo = 'TXN-' . date('Y') . '-' . str_pad($transaction->transaction_id, 4, '0', STR_PAD_LEFT);
                      $description = $transaction->description ?? 'Vendor Payment';
                    }
                @endphp
                <tr>
                    <td>{{ $index++ }}</td>
                    @php $plDate = $transaction->purchase_date ?? $transaction->return_date ?? $transaction->transaction_date ?? null; @endphp
                    <td>{{ $plDate ? \Carbon\Carbon::parse($plDate)->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $refNo }}</td>
                    <td>{{ strip_tags($description) }}</td>
                    <td class="text-right amount">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                    <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right amount">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right amount">{{ number_format($totalCredit, 2) }}</td>
                <td class="text-right amount">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@else
<div class="items-section avoid-break">
    <p style="text-align: center; color: #999;">No purchase ledger records found for the selected period.</p>
</div>
@endif

@endsection


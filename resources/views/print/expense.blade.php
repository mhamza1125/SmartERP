@extends('print.layout-voucher')

@section('title', 'Expense_Voucher_' . ($expense['transaction_id'] ?? 'N/A') . '_' . ($expense['transaction_date'] ?? date('d-m-Y')))

@section('content')

<div class="document-title">Expense Voucher</div>

{{-- Expense Header Information --}}
<div class="watermark-container">
    <div class="document-info">
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Voucher No:</span>
                <span class="info-value voucher-number">TXN-{{ date('Y') }}-{{ str_pad($expense['transaction_id'], 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $expense['transaction_date'] ?? 'N/A' }}</span>
            </div>
            {{-- <div class="info-row">
                <span class="info-label">Expense Head/Category:</span>
                <span class="info-value">{{ $expense['hname'] ?? 'N/A' }}</span>
            </div> --}}
            {{-- <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">{{ $expense['bank_id'] ? 'Bank Transfer' : 'Cash' }}</span>
            </div> --}}
        </div>

        <div class="info-section">
            @if($expense['bank_id'])
            <div class="info-row">
                <span class="info-label">Payment Bank:</span>
                <span class="info-value">{{ $expense['bname'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Title:</span>
                <span class="info-value">{{ $expense['account_title'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Account No:</span>
                <span class="info-value">{{ $expense['account'] ?? 'N/A' }}</span>
            </div>
            @endif
            {{-- <div class="info-row">
                <span class="info-label">Amount:</span>
                <span class="info-value amount">{{ number_format($expense['debit'] ?? $expense['credit'] ?? 0, 2) }}</span>
            </div> --}}
        </div>
    </div>

{{-- Expense Details Table --}}
<div class="avoid-break" >
    <h3 style="margin-bottom: 8px;">Expense Details</h3>
    <table class="print-table" style="font-size: 11px;">
        <thead>
            <tr>
                <th style="width: 50%; padding: 4px;">Description</th>
                <th style="width: 25%; padding: 4px;">Debit Amount</th>
                <th style="width: 25%; padding: 4px;">Credit Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                {{-- <td style="padding: 4px;">{{ strip_tags($expense['description'] ?? 'Expense Transaction') }}</td> --}}
                <td style="padding: 4px;">{{ $expense['hname'] ?? 'Expense Transaction' }} {{ strip_tags($expense['description'] ?? '') }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['credit'] ? number_format($expense['credit'], 2) : '-' }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['debit'] ? number_format($expense['debit'], 2) : '-' }}</td>
            </tr>
            @if($expense['bank_id'])
            <tr>
                <td style="padding: 4px;">{{ $expense['bname'] ?? 'Bank Account' }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['debit'] ? number_format($expense['debit'], 2) : '-' }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['credit'] ? number_format($expense['credit'], 2) : '-' }}</td>
            </tr>
            @else
            <tr>
                <td style="padding: 4px;">Cash Account</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['debit'] ? number_format($expense['debit'], 2) : '-' }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ $expense['credit'] ? number_format($expense['credit'], 2) : '-' }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td class="text-right" style="padding: 4px;">Total:</td>
                <td class="text-right amount" style="padding: 4px;">{{ number_format($expense['debit'] ?? $expense['credit'] ?? 0, 2) }}</td>
                <td class="text-right amount" style="padding: 4px;">{{ number_format($expense['credit'] ?? $expense['debit'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Expense Summary --}}
<div class="totals-section avoid-break" style="margin-top: 8px; margin-bottom: 8px;">
    <div class="total-row grand-total">
        <span>Expense Amount: &nbsp;&nbsp; </span>
        <span class="amount">{{ number_format(($expense['debit'] > 0 ? $expense['debit'] : $expense['credit']) ?? 0, 2) }}</span>
    </div>
</div>

{{-- Amount in Words --}}
@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break" style="margin-bottom: 8px;">
    <span class="amount-words-label">Amount in Words:</span>
    <span>{{ numberToWordsWithCurrency(($expense['debit'] > 0 ? $expense['debit'] : $expense['credit']) ?? 0) }}</span>
    {{-- <div style="font-size: 11px;">{{ numberToWordsWithCurrency(($expense['debit'] > 0 ? $expense['debit'] : $expense['credit']) ?? 0) }}</div> --}}
</div>
@endif

{{-- Signatures --}}
<div class="signatures avoid-break" style="margin-top: 8px;">
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line-sm"></div>
        <div style="font-size: 10px;">Prepared By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line-sm"></div>
        <div style="font-size: 10px;">Verified By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line-sm"></div>
        <div style="font-size: 10px;">Approved By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line-sm"></div>
        <div style="font-size: 10px;">Received By</div>
    </div>
</div>

</div>
@endsection

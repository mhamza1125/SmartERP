@extends('print.layout-voucher')

@section('title', 'Payment_Voucher_' . ($voucherNumber ?? 'N/A') . '_' . ($transaction['transaction_date'] ?? date('Y-m-d')))

@section('content')
<style>
    .my-div {
        position: relative;
        z-index: 1;
    }

    .my-div::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("{{ asset('assets/print-logo2.png') }}");
        background-repeat: no-repeat;
        background-position: center;
        /* background-size: contain; */
        background-size: 650px; 
        opacity: 0.1; /* adjust transparency */
        z-index: 0;
    }

    .my-div > * {
        position: relative;
        z-index: 1;
    }
</style>
{{-- <div class="company-logo-section">
    <img src="{{ asset('assets/print-logo.png') }}" alt="Company Logo" class="company-logo">
</div> --}}
<div class="document-title">Payment Voucher</div>

{{-- Voucher Header Information --}}
<div class="my-div">
    <div class="document-info">
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Voucher No:</span>
                <span class="info-value voucher-number">{{ $voucherNumber ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $transaction['transaction_date'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Transaction Type:</span>
                <span class="info-value">
                    @if($transaction['transaction_to'] == 'brs')
                        Balance Adjustment
                    @elseif($transaction['transaction_to'] == 'employee')
                        @if($transaction['transaction_type'] == 'salary')
                            Salary Payment
                        @elseif($transaction['transaction_type'] == 'wages')
                            Wages Payment
                        @elseif($transaction['transaction_type'] == 'advance')
                            Advance Payment to Employee
                        @elseif($transaction['transaction_type'] == 'receiveAdvance')
                            Loan Received from Employee
                        @else
                            {{ ucfirst($transaction['transaction_type'] ?? 'Employee Payment') }}
                        @endif
                    @elseif($transaction['transaction_to'] == 'customer')
                        @if($transaction['transaction_type'] == 'orderPayment')
                            Order Payment Received
                        @elseif($transaction['transaction_type'] == 'receiveAdvance')
                            Advance Payment Received
                        @else
                            Customer Payment
                        @endif
                    @else
                        {{ ucfirst($transaction['transaction_to'] ?? 'N/A') }} Payment
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">{{ $transaction['bank_id'] ? 'Bank Transfer' : 'Cash' }}</span>
            </div>
        </div>

        <div class="info-section">
            @if($transaction['transaction_to'] == 'vendor')
                <div class="info-row">
                    <span class="info-label">Vendor:</span>
                    <span class="info-value">{{ $transaction['vendor_no'] ?? '' }} - {{ $transaction['fname'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment For:</span>
                    <span class="info-value">
                        @if($transaction['transaction_type'] == 'wages')
                            Vendor Services/Wages
                        @elseif(isset($transaction['order_id']) && $transaction['order_id'])
                            Purchase #{{ $transaction['order_id'] }}
                            @if(isset($transaction['purchase_no']))
                                ({{ $transaction['purchase_no'] }})
                            @endif
                        @else
                            Vendor Services/Bills
                        @endif
                    </span>
                </div>
            @elseif($transaction['transaction_to'] == 'employee')
                <div class="info-row">
                    <span class="info-label">Employee:</span>
                    <span class="info-value">{{ $transaction['employee_no'] ?? '' }} - {{ $transaction['name'] ?? 'N/A' }}</span>
                </div>
            @elseif($transaction['transaction_to'] == 'customer')
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span class="info-value">{{ $transaction['customer_no'] ?? '' }} - {{ $transaction['fname'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment For:</span>
                    <span class="info-value">
                        @if($transaction['transaction_type'] == 'orderPayment' && isset($transaction['order_id']) && $transaction['order_id'])
                            Order #{{ $transaction['order_id'] }}
                            @if(isset($transaction['order_date']))
                                ({{ $transaction['order_date'] }})
                            @endif
                        @elseif($transaction['transaction_type'] == 'receiveAdvance')
                            Advance Payment from Customer
                        @else
                            Customer Payment
                        @endif
                    </span>
                </div>
            @elseif($transaction['transaction_to'] == 'contractor')
                <div class="info-row">
                    <span class="info-label">Contractor:</span>
                    <span class="info-value">{{ $transaction['vendor_no'] ?? '' }} - {{ $transaction['fname'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment For:</span>
                    <span class="info-value">
                        @if($transaction['transaction_type'] == 'wages')
                            Contractor Wages/Services
                        @elseif(isset($transaction['order_id']) && $transaction['order_id'])
                            Purchase #{{ $transaction['order_id'] }}
                            @if(isset($transaction['purchase_no']))
                                ({{ $transaction['purchase_no'] }})
                            @endif
                        @else
                            Contractor Services
                        @endif
                    </span>
                </div>
            @elseif($transaction['transaction_to'] == 'brs')
                <div class="info-row">
                    <span class="info-label">Adjustment Type:</span>
                    <span class="info-value">
                        {{-- This is temporary reversed --}}
                        @if($transaction['debit'] < 0)
                            Decrease Balance
                        @else
                            Increase Balance
                        @endif
                    </span>
                </div>
            @elseif($transaction['transaction_type'] == 'generalVoucher')
                @if($transaction['transaction_to'] == 'vendor')
                    <div class="info-row">
                        <span class="info-label">Vendor:</span>
                        <span class="info-value">{{ $transaction['vendor_no'] ?? '' }} - {{ $transaction['vendor_name'] ?? $transaction['fname'] ?? 'N/A' }}</span>
                    </div>
                @elseif($transaction['transaction_to'] == 'contractor')
                    <div class="info-row">
                        <span class="info-label">Contractor:</span>
                        <span class="info-value">{{ $transaction['vendor_no'] ?? '' }} - {{ $transaction['vendor_name'] ?? $transaction['fname'] ?? 'N/A' }}</span>
                    </div>
                @elseif($transaction['transaction_to'] == 'employee')
                    <div class="info-row">
                        <span class="info-label">Employee:</span>
                        <span class="info-value">{{ $transaction['employee_no'] ?? '' }} - {{ $transaction['employee_name'] ?? $transaction['name'] ?? 'N/A' }}</span>
                    </div>
                @elseif($transaction['transaction_to'] == 'customer')
                    <div class="info-row">
                        <span class="info-label">Customer:</span>
                        <span class="info-value">{{ $transaction['customer_no'] ?? '' }} - {{ $transaction['customer_name'] ?? $transaction['fname'] ?? 'N/A' }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Charge Description:</span>
                    <span class="info-value">{{ $transaction['description'] ?? 'General Voucher Charge' }}</span>
                </div>
            @endif

            @if($transaction['bank_id'])
                <div class="info-row">
                    <span class="info-label">Payment Bank:</span>
                    <span class="info-value">{{ $transaction['bname'] ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account Title:</span>
                    <span class="info-value">{{ $transaction['account_title'] ?? 'N/A' }}</span>
                </div>
                {{-- <div class="info-row">
                    <span class="info-label">Account No:</span>
                    <span class="info-value">{{ $transaction['account'] ?? 'N/A' }}</span>
                </div> --}}
            @endif

            {{-- Customer Payment Breakdown (inline) --}}
            @if($transaction['transaction_to'] == 'customer')
                @php
                    $grossAmount = $transaction['debit'] ?? $transaction['credit'] ?? 0;
                    $netAmount = $grossAmount - ($transaction['fees_expenses'] ?? 0);
                @endphp
                @if($grossAmount > 0)
                <div class="info-row">
                    <span class="info-label">Amount Received:</span>
                    <span class="info-value amount">{{ number_format($grossAmount, 2) }}</span>
                </div>
                @if(isset($transaction['fees_expenses']) && $transaction['fees_expenses'] > 0)
                <div class="info-row">
                    <span class="info-label">Fees/Expenses:</span>
                    <span class="info-value amount">{{ number_format($transaction['fees_expenses'], 2) }}</span>
                </div>
                <div class="info-row" style="font-weight: bold; background-color: #f5f5f5; padding: 5px;">
                    <span class="info-label">Net Amount:</span>
                    <span class="info-value amount">{{ number_format($netAmount, 2) }}</span>
                </div>
                @endif
                @endif
            @endif
        </div>
    </div>



{{-- Payment Details Table --}}
<div class="avoid-break" style="margin-top: -25px">
    <h3>Payment Details</h3>
    @if($transaction['transaction_to'] == 'brs')
        {{-- Balance Adjustment: Show only one column based on adjustment type --}}
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 50%">
                        @if($transaction['debit'] > 0)
                        Increase Amount
                        @else
                        Decrease Amount
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bank Reconciliation Adjustment</td>
                    <td class="text-right amount">
                        @if($transaction['debit'] > 0)
                            {{ number_format($transaction['debit'], 2) }}
                        @else
                            {{ number_format($transaction['credit'], 2) }}
                        @endif
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f5f5f5;">
                    <td class="text-right">Total:</td>
                    <td class="text-right amount">{{ number_format(($transaction['debit'] > 0 ? $transaction['debit'] : $transaction['credit']) ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        {{-- Regular transactions: Show both columns --}}
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 25%">Debit Amount</th>
                    <th style="width: 25%">Credit Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if($transaction['transaction_to'] == 'customer' && isset($transaction['order_id']))
                            Payment for Order #{{ $transaction['order_id'] ?? 'N/A' }}
                            @if(isset($transaction['order_date']))
                                ({{ $transaction['order_date'] }})
                            @endif
                        @else
                            Payment Transaction
                            {{-- {{ $transaction['description'] ?? 'Payment Transaction' }} --}}
                        @endif
                    </td>
                    <td class="text-right amount">{{ $transaction['credit'] ? number_format($transaction['credit'], 2) : '-' }}</td>
                    <td class="text-right amount">{{ $transaction['debit'] ? number_format($transaction['debit'], 2) : '-' }}</td>
                </tr>
                @if($transaction['bank_id'])
                <tr>
                    <td>{{ $transaction['bname'] ?? 'Bank Account' }} - {!! $transaction['description'] ?? '' !!}</td>
                    <td class="text-right amount">{{ $transaction['debit'] ? number_format($transaction['debit'], 2) : '-' }}</td>
                    <td class="text-right amount">{{ $transaction['credit'] ? number_format($transaction['credit'], 2) : '-' }}</td>
                </tr>
                @else
                <tr>
                    <td>Cash Account</td>
                    <td class="text-right amount">{{ $transaction['debit'] ? number_format($transaction['debit'], 2) : '-' }}</td>
                    <td class="text-right amount">{{ $transaction['credit'] ? number_format($transaction['credit'], 2) : '-' }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
           <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td class="text-right">Total:</td>
                <td class="text-right amount">
                    {{ number_format(!empty($transaction['debit']) && $transaction['debit'] != 0 ? $transaction['debit'] : (!empty($transaction['credit']) && $transaction['credit'] != 0 ? $transaction['credit'] : 0), 2) }}
                </td>
                <td class="text-right amount">
                    {{ number_format(!empty($transaction['debit']) && $transaction['debit'] != 0 ? $transaction['debit'] : (!empty($transaction['credit']) && $transaction['credit'] != 0 ? $transaction['credit'] : 0), 2) }}
                </td>
            </tr>

            </tfoot>
        </table>
    @endif
</div>



{{-- Payment Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Payment Amount: &nbsp &nbsp </span>
        <span class="amount">{{ number_format(($transaction['debit'] > 0 ? $transaction['debit'] : $transaction['credit']) ?? 0, 2) }}</span>
    </div>
</div>

{{-- Amount in Words --}}
@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break" style="margin-top: 8px; margin-bottom: 8px;">
    <div class="amount-words-label">Amount in Words:</div>
    <div>{{ numberToWordsWithCurrency(($transaction['debit'] > 0 ? $transaction['debit'] : $transaction['credit']) ?? 0) }}</div>
</div>
@endif

{{-- Signatures --}}
<div class="signatures avoid-break" style="margin-top: 8px;">
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line" style="margin-right: 15px; height: 20px;"></div>
        <div style="font-size: 10px;">Prepared By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line" style="margin-right: 15px; height: 20px;"></div>
        <div style="font-size: 10px;">Verified By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line" style="margin-right: 15px; height: 20px;"></div>
        <div style="font-size: 10px;">Approved By</div>
    </div>
    <div class="signature-box" style="padding: 4px;">
        <div class="signature-line" style="height: 20px;"></div>
        <div style="font-size: 10px;">Received By</div>
    </div>
</div>

</div>
@endsection

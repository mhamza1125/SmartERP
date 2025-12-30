@extends('print.layout')

@section('title', 'Proforma_Invoice_' . ($order['order_no'] ?? 'N/A') . '_' . ($order['order_date'] ?? date('Y-m-d')))

@section('content')
<div class="document-title">Proforma Invoice</div>

<style>
    .document-info {
        display: flex;
        width: 100%;
    }

    .info-section {
        width: 50%; /* static 50% width for each column */
        box-sizing: border-box;
        padding: 0 10px; /* optional padding between columns */
    }
</style>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $order['fname'] ?? 'N/A' }} {{ $order['lname'] ?? '' }}</span>
        </div>
        @if(isset($order['address']) && !empty($order['address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $order['address'] }}</span>
        </div>
        @endif
        {{-- Company info (no title) --}}
        @if(isset($company))
            @if(!empty($company->ntn))
            <div class="info-row">
                <span class="info-label">NTN:</span>
                <span class="info-value">{{ $company->ntn }}</span>
            </div>
            @endif

            @if(!empty($company->rex_no))
            <div class="info-row">
                <span class="info-label">REX No:</span>
                <span class="info-value">{{ $company->rex_no }}</span>
            </div>
            @endif
            @if(isset($sellingType) && !empty($sellingType))
                <div class="info-row">
                    <span class="info-label">Selling Type:</span>
                    <span class="info-value">{{ $sellingType }}</span>
                </div>
                @endif
                @if(isset($uom) && !empty($uom))
                <div class="info-row">
                    <span class="info-label">UOM:</span>
                    <span class="info-value">{{ $uom }}</span>
                </div>
            @endif
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice No:</span>
            <span class="info-value">{{ $order['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value">{{ $order['order_date'] ?? 'N/A' }}</span>
        </div>
        @if(isset($order['due_date']) && !empty($order['due_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ $order['due_date'] }}</span>
        </div>
        @endif
        @if(isset($hsCode) && !empty($hsCode))
        <div class="info-row">
            <span class="info-label">HS Code:</span>
            <span class="info-value">{{ $hsCode }}</span>
        </div>
        @endif
        @php
            $totalQuantity = $orderItem->sum('quantity');
        @endphp
        <div class="info-row">
            <span class="info-label">Total Quantity:</span>
            <span class="info-value">{{ $totalQuantity }}</span>
        </div>
    </div>
</div>

{{-- Total Quantity Display --}}
{{-- <div class="document-info">
    <div class="info-section">
        
    </div>
</div> --}}

{{-- Order Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <h3>Invoice Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 11%">Article No</th>
                <th style="width: 24%">Product Name</th>
                <th style="width: 10%">Size</th>
                {{-- <th style="width: 10%">Unit</th> --}}
                <th style="width: 10%">Quantity</th>
                <th style="width: 12%">Unit Price</th>
                <th style="width: 16%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($orderItem->count())
                @php $product_id = 0; @endphp
                @foreach($orderItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="2"></td>
                    @else
                        <td class="text-center">{{ $item->article_no }}</td>
                        <td class="text-center">{{ $item->pname }}</td>
                        @php $product_id = $item->product_id; @endphp
                    @endif
                    <td class="text-center">{{ $item->name }}</td>
                    {{-- <td class="text-center">{{ $item->uname ?? 'N/A' }}</td> --}}
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price ?? 0, 2) }} {{-- {{ $item->cname }} --}}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price ?? 0), 2) }} {{-- {{ $item->cname }} --}}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Invoice Totals --}}
@php
$totalOriginal = $orderItem->sum(function($item) {
    return $item->quantity * ($item->price ?? 0);
});
$firstItem = $orderItem->first();
$currencyName = $firstItem->cname ?? 'PKR';
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:
            @if(function_exists('numberToWordsWithCurrency'))
                {{ numberToWordsWithCurrency($totalOriginal ?? 0) }} {{ $currencyName }}
            @endif
        </span> 
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>

{{-- Bank Account Details --}}
@if(isset($bankDetails) && !empty($bankDetails))
<div class="info-section1 avoid-break">
    <h3>Bank Account Details</h3>
    <table class="print-table">
        <tbody>
            <tr>
                <td><strong>Account Title:</strong></td>
                <td>{{ $bankDetails['account_title'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Account Number:</strong></td>
                <td>{{ $bankDetails['account'] ?? 'N/A' }}</td>
            </tr>
            @if(isset($bankDetails['iban']) && !empty($bankDetails['iban']))
            <tr>
                <td><strong>IBAN:</strong></td>
                <td>{{ $bankDetails['iban'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['swift_code']) && !empty($bankDetails['swift_code']))
            <tr>
                <td><strong>SWIFT Code:</strong></td>
                <td>{{ $bankDetails['swift_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['branch_code']) && !empty($bankDetails['branch_code']))
            <tr>
                <td><strong>Branch Code:</strong></td>
                <td>{{ $bankDetails['branch_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['address']) && !empty($bankDetails['address']))
            <tr>
                <td><strong>Bank Address:</strong></td>
                <td>{{ $bankDetails['address'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Certification Statement --}}
<div class="certification-statement avoid-break" style="margin-top: 30px; text-align: center; font-weight: bold;">
    <p>CERTIFIED TO BE TRUE AND CORRECT: {{ $company->name ?? 'SAJJADSON LAB EQUIPMENT' }}</p>
</div>

@endsection

@extends('print.layout')

@section('title', 'Commercial_Invoice_' . ($delivery['delivery_no'] ?? $delivery['cust_no'] ?? 'N/A') . '_' . date('d-m-Y'))

@push('styles')
<style>
    .document-info { display: flex; width: 100%; }
    .info-section   { width: 50%; box-sizing: border-box; }
    .full-width-section { width: 100%; box-sizing: border-box; }
</style>
@endpush

@section('content')
<div class="document-title">Commercial Invoice</div>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $delivery['fname'] ?? 'N/A' }} {{ $delivery['lname'] ?? '' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Address:</span>
            @if(isset($delivery['tshipping']) && !empty($delivery['tshipping']))
                <span class="info-value">{{ $delivery['tshipping'] }}</span>
            @else
                <span class="info-value">{{ $delivery['address'] ?? 'N/A' }}</span>
            @endif
        </div>

        <div class="info-row">
            <span class="info-label">Destination Port:</span>
            <span class="info-value">{{ $delivery['tport_no'] ?? 'N/A' }}</span>
        </div>

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
        @endif

        @if(isset($hsCode) && !empty($hsCode))
        <div class="info-row">
            <span class="info-label">HS Code:</span>
            <span class="info-value">{{ $hsCode }}</span>
        </div>
        @endif

        @if(!empty($delivery['fi_no']))
            <div class="info-row">
                <span class="info-label">FI No:</span>
                <span class="info-value">{{ $delivery['fi_no'] }}</span>
            </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value">{{ date('d-m-Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice No:</span>
            <span class="info-value">{{ $delivery['delivery_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($delivery['delivery_date']) && !empty($delivery['delivery_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($delivery['delivery_date'])->format('d-m-Y') }}</span>
        </div>
        @endif
        @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 0)
        <div class="info-row">
            <span class="info-label">Order Numbers:</span>
            <span class="info-value">
                @php
                    $orderNumbers = collect($relatedOrders)->pluck('order_no')->implode(', ');
                @endphp
                {{ $orderNumbers }}
            </span>
        </div>
        @else
        <div class="info-row">
            <span class="info-label">Order Number:</span>
            <span class="info-value">{{ $delivery['order_no'] ?? 'N/A' }}</span>
        </div>
        @endif

        @if(isset($uom) && !empty($uom))
        <div class="info-row">
            <span class="info-label">UOM:</span>
            <span class="info-value">{{ $uom }}</span>
        </div>
        @endif

        @php
            $totalQuantity = $deliveryItem->sum('quantity');
        @endphp
        <div class="info-row">
            <span class="info-label">Total Quantity:</span>
            <span class="info-value">{{ $totalQuantity }}</span>
        </div>
        @if(isset($packingList) && $packingList && isset($packingList['carton_count']))
        <div class="info-row">
            <span class="info-label">Total Packages:</span>
            <span class="info-value">{{ $packingList['carton_count'] }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Delivered Items Table --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 12%">Article No</th>
                <th style="width: 28%">Product Name</th>
                <th style="width: 10%">Size</th>
                {{-- <th style="width: 10%">Unit</th> --}}
                <th style="width: 10%">Quantity</th>
                <th style="width: 13%">Unit Price</th>
                <th style="width: 13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryItem->count())
                @foreach($deliveryItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    <td class="text-center">{{ $item->article_no }}</td>
                    <td class="text-left">{{ $item->name }}</td>
                    <td class="text-center">{{ $item->hname ?? 'N/A' }}</td>
                    {{-- <td class="text-center">{{ $item->puname ?? 'N/A' }}</td> --}}
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price ?? 0, 2) }}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

{{-- Invoice Totals --}}
@php
$totalOriginal = $deliveryItem->sum(function($item) {
    return $item->quantity * ($item->price ?? 0);
});
$firstItem = $deliveryItem->first();
$currencyName = $firstItem->cname ?? 'PKR';

// Determine the total label based on selling type
$totalLabel = 'Grand Total:';
if (isset($sellingType) && !empty($sellingType)) {
    $totalLabel = 'Total ' . $sellingType . ' Amount:';
}
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>{{ $totalLabel }}
            @if(function_exists('numberToWordsWithCurrency'))
                {{ numberToWordsWithCurrency($totalOriginal ?? 0) }} {{ $currencyName }}
            @endif
        </span>
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>

{{-- Statement of Origin (if provided) --}}
@if(isset($statementOfOrigin) && !empty($statementOfOrigin))
<div class="avoid-break" style="margin-top: 15px;">
    <h3>Statement of Origin</h3>
    <p>{{ $statementOfOrigin }}</p>
</div>
@endif

{{-- Bank Account Details --}}
@if(isset($bankDetails) && !empty($bankDetails))
<div class="avoid-break" style="margin-top: 15px;">
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
<div class="certification-statement avoid-break">
    <p>CERTIFIED TO BE TRUE AND CORRECT: {{ $company->name ?? '' }}</p>
</div>
@endif

@endsection

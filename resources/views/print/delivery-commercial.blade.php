@extends('print.layout')

@section('title', 'Commercial_Invoice_' . ($delivery['delivery_no'] ?? $delivery['cust_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Commercial Invoice')
@section('doc_no', $delivery['delivery_no'] ?? '')

@push('styles')
<style>
    .document-info { display: flex; width: 100%; }
    .info-section   { width: 50%; box-sizing: border-box; }
</style>
@endpush

@section('content')
@php
    $totalQuantity = $deliveryItem->sum('quantity');
    $itemsTotal = $deliveryItem->sum(fn ($item) => $item->quantity * ($item->price ?? 0));
    $freightCharges = (float) ($delivery['freight_charges'] ?? 0);
    $grandTotal = $itemsTotal + $freightCharges;
    $currencyName = $deliveryItem->first()->cname ?? 'PKR';
    $totalLabel = !empty($sellingType) ? 'Total ' . $sellingType . ' Amount:' : 'Grand Total:';

    // A delivery's own FI No wins; otherwise the default from Settings > Company
    $fiNo = !empty($delivery['fi_no']) ? $delivery['fi_no'] : ($company->fi_no ?? null);

    // Payment terms of the order(s) this delivery is for
    $paymentTerms = (!empty($isMultiOrder) && !empty($relatedOrders))
        ? collect($relatedOrders)->map(fn ($order) => trim((string) ((array) $order)['payment_terms']))->filter()->unique()->implode('; ')
        : trim((string) ($delivery['payment_terms'] ?? ''));
@endphp

{{-- Customer / invoice details --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $delivery['fname'] ?? 'N/A' }} {{ $delivery['lname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ !empty($delivery['tshipping']) ? $delivery['tshipping'] : ($delivery['address'] ?? 'N/A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Destination Port:</span>
            <span class="info-value">{{ $delivery['tport_no'] ?? 'N/A' }}</span>
        </div>
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
        @if(!empty($fiNo))
        <div class="info-row">
            <span class="info-label">FI No:</span>
            <span class="info-value">{{ $fiNo }}</span>
        </div>
        @endif
        @if(!empty($hsCode))
        <div class="info-row">
            <span class="info-label">HS Code:</span>
            <span class="info-value">{{ $hsCode }}</span>
        </div>
        @endif
        @if($paymentTerms !== '')
        <div class="info-row">
            <span class="info-label">Payment Terms:</span>
            <span class="info-value">{{ $paymentTerms }}</span>
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
        @if(!empty($delivery['delivery_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ formatDate($delivery['delivery_date']) }}</span>
        </div>
        @endif
        <div class="info-row">
            @if(!empty($isMultiOrder) && !empty($relatedOrders))
                <span class="info-label">Order Numbers:</span>
                <span class="info-value">{{ collect($relatedOrders)->pluck('order_no')->implode(', ') }}</span>
            @else
                <span class="info-label">Order Number:</span>
                <span class="info-value">{{ $delivery['order_no'] ?? 'N/A' }}</span>
            @endif
        </div>
        @if(!empty($uom))
        <div class="info-row">
            <span class="info-label">UOM:</span>
            <span class="info-value">{{ $uom }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total QTY:</span>
            <span class="info-value">{{ number_format($totalQuantity) }}</span>
        </div>
        @if(!empty($packingList['carton_count']))
        <div class="info-row">
            <span class="info-label">Total Packages:</span>
            <span class="info-value">{{ $packingList['carton_count'] }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Delivered Items Table --}}
@if($deliveryItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">Sr.</th>
                <th class="text-center" style="width: 12%">Article No</th>
                <th class="text-left" style="width: 27%">Product Name</th>
                <th class="text-center" style="width: 16%">Size</th>
                <th class="num" style="width: 10%">QTY</th>
                <th class="num" style="width: 13%">Price</th>
                <th class="num" style="width: 17%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryItem as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->article_no }}</td>
                <td class="text-left">{{ $item->name }}</td>
                <td class="text-center">{{ $item->hname ?? 'N/A' }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="num">{{ number_format($item->quantity * ($item->price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Items Total</td>
                <td class="num">{{ number_format($totalQuantity) }}</td>
                <td></td>
                <td class="num">{{ number_format($itemsTotal, 2) }}</td>
            </tr>
            @if($freightCharges > 0)
            <tr>
                <td colspan="6" class="text-right">Freight Charges</td>
                <td class="num">{{ number_format($freightCharges, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>
</div>

{{-- Invoice Totals --}}
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>{{ $totalLabel }}
            @if(function_exists('numberToWordsWithCurrency'))
                {{ numberToWordsWithCurrency($grandTotal) }} {{ $currencyName }}
            @endif
        </span>
        <span class="amount">{{ number_format($grandTotal, 2) }} {{ $currencyName }}</span>
    </div>
</div>

{{-- Statement of Origin (if provided) --}}
@if(!empty($statementOfOrigin))
<div class="avoid-break" style="margin-top: 15px;">
    <div class="table-title">Statement of Origin</div>
    <p>{{ $statementOfOrigin }}</p>
</div>
@endif

@include('print.partials.bank-details')

{{-- Certification Statement --}}
<div class="certification-statement avoid-break">
    <p>CERTIFIED TO BE TRUE AND CORRECT</p>
    <p>{{ strtoupper($company->name ?? '') }}</p>
</div>
@endif

@endsection

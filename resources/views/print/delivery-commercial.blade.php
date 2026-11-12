@extends('print.layout')

@section('title', 'Commercial_Invoice_' . ($delivery['delivery_no'] ?? $delivery['cust_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Commercial Invoice')
@section('doc_no', $delivery['delivery_no'] ?? '')

@push('styles')
<style>
    /* Long statements (even without spaces) wrap inside the page width; line breaks are kept */
    .statement-of-origin   { margin-top: 15px; width: 100%; max-width: 100%; }
    .statement-of-origin p { white-space: pre-line; overflow-wrap: anywhere; word-break: break-word; }
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

    // FI No is resolved by the controller: modal value, else the delivery's, else Settings > Company
    $fiNo = $fiNo ?? null;

    // Partial payment: the invoice still shows the full value of the goods, plus the share requested now
    $paymentPercent = (float) ($paymentPercent ?? 100);
    $isPartialPayment = $paymentPercent < 100;
    $percentLabel = rtrim(rtrim(number_format($paymentPercent, 2), '0'), '.') . '%';
    $amountDue = round($grandTotal * $paymentPercent / 100, 2);

    // Combined invoice for several orders: an Order column identifies each line's order
    $showLineOrders = !empty($showLineOrders);

    // Payment terms of the order(s) this delivery is for
    $paymentTerms = (!empty($isMultiOrder) && !empty($relatedOrders))
        ? collect($relatedOrders)->map(fn ($order) => trim((string) ((array) $order)['payment_terms']))->filter()->unique()->implode('; ')
        : trim((string) ($delivery['payment_terms'] ?? ''));
@endphp

{{-- Invoice details --}}
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">Invoice No</span>
        <span class="meta-value mono">{{ $delivery['delivery_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Invoice Date</span>
        <span class="meta-value mono">{{ date('d-m-Y') }}</span>
    </div>
    <div class="meta-field">
        @if(!empty($isMultiOrder) && !empty($relatedOrders))
            <span class="meta-label">Order Numbers</span>
            <span class="meta-value mono">{{ collect($relatedOrders)->map(fn ($order) => ((array) $order)['order_no'] ?? '')->filter()->implode(', ') }}</span>
        @else
            <span class="meta-label">Order Number</span>
            <span class="meta-value mono">{{ $delivery['order_no'] ?? 'N/A' }}</span>
        @endif
    </div>
    <div class="meta-field">
        <span class="meta-label">Total QTY</span>
        <span class="meta-value mono">{{ number_format($totalQuantity) }}</span>
    </div>
    @if(!empty($packingList['carton_count']))
    <div class="meta-field">
        <span class="meta-label">Total Packages</span>
        <span class="meta-value mono">{{ $packingList['carton_count'] }}</span>
    </div>
    @endif
    @if(!empty($uom))
    <div class="meta-field">
        <span class="meta-label">UOM</span>
        <span class="meta-value">{{ $uom }}</span>
    </div>
    @endif
    @if(!empty($hsCode))
    <div class="meta-field">
        <span class="meta-label">HS Code</span>
        <span class="meta-value mono">{{ $hsCode }}</span>
    </div>
    @endif
    @if(!empty($fiNo))
    <div class="meta-field">
        <span class="meta-label">FI No</span>
        <span class="meta-value mono">{{ $fiNo }}</span>
    </div>
    @endif
    @if(!empty($company->ntn))
    <div class="meta-field">
        <span class="meta-label">NTN</span>
        <span class="meta-value mono">{{ $company->ntn }}</span>
    </div>
    @endif
    @if(!empty($company->rex_no))
    <div class="meta-field">
        <span class="meta-label">REX No</span>
        <span class="meta-value mono">{{ $company->rex_no }}</span>
    </div>
    @endif
    @if($paymentTerms !== '')
    <div class="meta-field">
        <span class="meta-label">Payment Terms</span>
        <span class="meta-value">{{ $paymentTerms }}</span>
    </div>
    @endif
    @if($isPartialPayment)
    <div class="meta-field">
        <span class="meta-label">Payment Requested</span>
        <span class="meta-value">{{ $percentLabel }} of invoice value</span>
    </div>
    @endif
</div>

{{-- Customer --}}
<div class="party-block party-block--spaced">
    <div class="party-label">CUSTOMER</div>
    <div class="party-name">{{ $delivery['fname'] ?? 'N/A' }} {{ $delivery['lname'] ?? '' }}</div>
    <div class="party-detail">{{ !empty($delivery['tshipping']) ? $delivery['tshipping'] : ($delivery['address'] ?? 'N/A') }}</div>
    <div class="party-contact">Destination Port: {{ $delivery['tport_no'] ?? 'N/A' }}</div>
</div>

{{-- Delivered Items Table --}}
@if($deliveryItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">Sr.</th>
                <th class="text-center" style="width: 12%">Article No</th>
                @if($showLineOrders)
                <th class="text-center" style="width: 13%">Order No</th>
                <th class="text-left" style="width: 20%">Product</th>
                <th class="text-center" style="width: 10%">Size</th>
                @else
                <th class="text-left" style="width: 27%">Product</th>
                <th class="text-center" style="width: 16%">Size</th>
                @endif
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
                @if($showLineOrders)
                <td class="text-center">{{ $item->line_order_no ?? '-' }}</td>
                @endif
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
                <td colspan="{{ $showLineOrders ? 5 : 4 }}" class="text-right">Items Total</td>
                <td class="num">{{ number_format($totalQuantity) }}</td>
                <td></td>
                <td class="num">{{ number_format($itemsTotal, 2) }}</td>
            </tr>
            @if($freightCharges > 0)
            <tr>
                <td colspan="{{ $showLineOrders ? 7 : 6 }}" class="text-right">Freight Charges</td>
                <td class="num">{{ number_format($freightCharges, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>
</div>

{{-- Invoice Totals --}}
<div class="totals-section avoid-break">
    @if($isPartialPayment)
    <div class="total-row">
        <span>Invoice Value ({{ trim($totalLabel, ':') }}):</span>
        <span class="amount">{{ number_format($grandTotal, 2) }} {{ $currencyName }}</span>
    </div>
    <div class="total-row grand-total">
        <span>Payment Due ({{ $percentLabel }} of Invoice Value):
            @if(function_exists('numberToWordsWithCurrency'))
                <span class="grand-total__words">{{ numberToWordsWithCurrency($amountDue) }} {{ $currencyName }}</span>
            @endif
        </span>
        <span class="amount">{{ number_format($amountDue, 2) }} {{ $currencyName }}</span>
    </div>
    @else
    <div class="total-row grand-total">
        <span>{{ $totalLabel }}
            @if(function_exists('numberToWordsWithCurrency'))
                <span class="grand-total__words">{{ numberToWordsWithCurrency($grandTotal) }} {{ $currencyName }}</span>
            @endif
        </span>
        <span class="amount">{{ number_format($grandTotal, 2) }} {{ $currencyName }}</span>
    </div>
    @endif
</div>

{{-- Statement of Origin (if provided) --}}
@if(!empty($statementOfOrigin))
<div class="avoid-break statement-of-origin">
    <div class="table-title">Statement of Origin</div>
    <p>{{ $statementOfOrigin }}</p>
</div>
@endif

@include('print.partials.bank-details')

{{-- Certification Statement --}}
<div class="certification-statement avoid-break">
    <p>CERTIFIED TO BE TRUE AND CORRECT @if(!empty($company->name))&ldquo;{{ strtoupper($company->name) }}&rdquo;@endif</p>
</div>
@endif

@endsection

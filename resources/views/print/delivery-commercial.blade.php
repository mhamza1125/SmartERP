@extends('print.layout')

@section('title', 'Commercial_Invoice_' . ($delivery['delivery_no'] ?? $delivery['cust  _no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Commercial Invoice</div>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $delivery['fname'] ?? 'N/A' }} {{ $delivery['lname'] ?? '' }}</span>
        </div>
        @if(isset($delivery['address']) && !empty($delivery['address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $delivery['address'] }}</span>
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
            <span class="info-value">{{ date('Y-m-d') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice No:</span>
            <span class="info-value">{{ $delivery['delivery_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($delivery['delivery_date']) && !empty($delivery['delivery_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ $delivery['delivery_date'] }}</span>
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
        @if(isset($hsCode) && !empty($hsCode))
        <div class="info-row">
            <span class="info-label">HS Code:</span>
            <span class="info-value">{{ $hsCode }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Delivered Items Table --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <h3>Delivered Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 12%">Article No</th>
                <th style="width: 28%">Product Name</th>
                <th style="width: 10%">Size</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 13%">Unit Price</th>
                <th style="width: 13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryItem->count())
                @php $product_id = 0; @endphp
                @foreach($deliveryItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="2"></td>
                    @else
                        <td class="text-center">{{ $item->article_no }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        @php $product_id = $item->product_id; @endphp
                    @endif
                    <td class="text-center">{{ $item->sname ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price2 ?? 0, 2) }} {{ $item->cname ?? 'PKR' }}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price2 ?? 0), 2) }} {{ $item->cname ?? 'PKR' }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

{{-- Invoice Totals --}}
@php
$totalOriginal = $deliveryItem->sum(function($item) {
    return $item->quantity * ($item->price2 ?? 0);
});
$firstItem = $deliveryItem->first();
$currencyName = $firstItem->cname ?? 'PKR';
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>
@endif

{{-- Statement of Origin (if provided) --}}
@if(isset($statementOfOrigin) && !empty($statementOfOrigin))
<div class="statement-of-origin avoid-break">
    <h3>Statement of Origin</h3>
    <p>{{ $statementOfOrigin }}</p>
</div>
@endif

@endsection


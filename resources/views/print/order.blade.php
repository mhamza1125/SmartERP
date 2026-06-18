@extends('print.layout')

@section('title', 'Order_' . ($order['order_no'] ?? 'N/A') . '_' . ($order['order_date'] ?? date('d-m-Y')))

@section('content')
<div class="document-title">Order Information</div>

{{-- Order Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer No:</span>
            <span class="info-value">{{ $order['customer_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $order['fname'] ?? 'N/A' }} {{ $order['lname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $order['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $order['job_no'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{ !empty($order['order_date']) ? \Carbon\Carbon::parse($order['order_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        @if(isset($order['due_date']) && !empty($order['due_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order['due_date'])->format('d-m-Y') }}</span>
        </div>
        @endif
        @if(isset($order['payment_terms']) && !empty($order['payment_terms']))
        <div class="info-row">
            <span class="info-label">Payment Terms:</span>
            <span class="info-value">{{ $order['payment_terms'] }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Order Status:</span>
            <span class="info-value">
                @if($order['order_status'] == 1) Draft
                @elseif($order['order_status'] == 2) Confirmed
                @elseif($order['order_status'] == 3) Dispatched
                @elseif($order['order_status'] == 4) Delivered
                @elseif($order['order_status'] == 5) Cancelled
                @else Unknown @endif
            </span>
        </div>
    </div>
</div>

{{-- Order Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 8%">Sr.</th>
                <th style="width: 15%">Article No</th>
                <th style="width: 25%">Product Name</th>
                <th style="width: 17%">Size</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 12%">Rate</th>
                <th style="width: 13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($orderItem->count())
                @foreach($orderItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    <td class="text-center">{{ $item->article_no }}</td>
                    <td class="text-left">{{ $item->pname }}</td>
                    <td class="text-center">{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price ?? 0, 2) }}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price ?? 0), 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Order Totals --}}
@php
$total = $orderItem->sum(function($item) {
    return $item->quantity * ($item->price ?? 0);
});
$firstItem = $orderItem->first();
$currencyName = $firstItem->cname ?? 'PKR';
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($total, 2) }} {{ $currencyName }}</span>
    </div>
</div>

{{-- Amount in Words --}}
@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <div class="amount-words-label">Amount in Words:</div>
    <div>{{ numberToWordsWithCurrency($total) }} {{ $currencyName }}</div>
</div>
@endif

{{-- Additional Information --}}
@if(isset($order['description']) && !empty($order['description']))
<div class="info-section avoid-break">
    <h3>Description/Notes</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ strip_tags($order['description']) }}
    </div>
</div>
@endif

@endsection

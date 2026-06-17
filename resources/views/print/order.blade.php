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
        @if(isset($order['email']) && !empty($order['email']))
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $order['email'] }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Voucher No:</span>
            <span class="info-value">TXN-{{ date('Y') }}-{{ str_pad($order['order_id'], 4, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $order['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $order['job_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{ $order['order_date'] ?? 'N/A' }}</span>
        </div>
        @if(isset($order['due_date']) && !empty($order['due_date']))
        <div class="info-row">
            <span class="info-label">Due Date:</span>
            <span class="info-value">{{ $order['due_date'] }}</span>
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
                @if($order['order_status'] == 1) <span class="status-badge status-badge-secondary">Draft</span>
                @elseif($order['order_status'] == 2) <span class="status-badge status-badge-success">Confirmed</span>
                @elseif($order['order_status'] == 3) <span class="status-badge status-badge-info">Dispatched</span>
                @elseif($order['order_status'] == 4) <span class="status-badge status-badge-primary">Delivered</span>
                @elseif($order['order_status'] == 5) <span class="status-badge status-badge-danger">Cancelled</span>
                @else <span class="status-badge status-badge-secondary">Unknown</span> @endif
            </span>
        </div>
    </div>
</div>

{{-- Order Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <h3>Order Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 8%">Sr.</th>
                <th style="width: 15%">Article No</th>
                <th style="width: 25%">Product</th>
                <th style="width: 17%">Size</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 12%">Rate</th>
                <th style="width: 13%">Total</th>
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
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($total, 2) }}</span>
    </div>
</div>

{{-- Amount in Words --}}
@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <div class="amount-words-label">Amount in Words:</div>
    <div>{{ numberToWordsWithCurrency($total) }}</div>
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

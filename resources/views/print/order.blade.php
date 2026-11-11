@extends('print.layout')

@section('title', 'Order_' . ($order['order_no'] ?? 'N/A') . '_' . formatDate($order['order_date'] ?? now()))
@section('doc_title', 'Order')
@section('doc_no', $order['order_no'] ?? '')

@section('content')

{{-- ── Meta band: two rows of three ───────────────────────────────────────── --}}
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">Order No</span>
        <span class="meta-value mono">{{ $order['order_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $order['job_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Order Date</span>
        <span class="meta-value mono">{{ formatDate($order['order_date'] ?? null, 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Delivery Date</span>
        <span class="meta-value mono">{{ formatDate($order['due_date'] ?? null) }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Payment Terms</span>
        <span class="meta-value">{{ !empty($order['payment_terms']) ? $order['payment_terms'] : '-' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Status</span>
        <span class="meta-value">
            @php
                $statusMap = [1 => 'Draft', 2 => 'Confirmed', 3 => 'Dispatched', 4 => 'Delivered', 5 => 'Cancelled'];
                $pillMap   = [1 => 'pill-secondary', 2 => 'pill-info', 3 => 'pill-warning', 4 => 'pill-success', 5 => 'pill-danger'];
                $s = $order['order_status'] ?? 0;
            @endphp
            <span class="status-pill {{ $pillMap[$s] ?? 'pill-secondary' }}">{{ $statusMap[$s] ?? 'Unknown' }}</span>
        </span>
    </div>
</div>

{{-- ── Customer ─────────────────────────────────────────────────────────────── --}}
<div class="party-block party-block--spaced">
    <div class="party-label">CUSTOMER</div>
    <div class="party-name">
        @if(!empty($order['customer_no'])){{ $order['customer_no'] }} — @endif{{ $order['fname'] ?? '' }} {{ $order['lname'] ?? '' }}
    </div>
</div>

{{-- ── Order Items ──────────────────────────────────────────────────────────── --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:6%">Sr.</th>
                <th style="width:13%" class="text-center">Article No</th>
                <th>Product</th>
                <th style="width:15%" class="text-center">Size</th>
                <th style="width:10%" class="text-right">QTY</th>
                <th style="width:12%" class="text-right">Price</th>
                <th style="width:13%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItem as $item)
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td class="code">{{ $item->article_no }}</td>
                <td>{{ $item->pname }}</td>
                <td class="text-center">{{ $item->name }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="num">{{ number_format($item->quantity * ($item->price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@php
    $total = $orderItem->sum(function($item) {
        return $item->quantity * ($item->price ?? 0);
    });
    $firstItem = $orderItem->first();
    $currencyName = $firstItem->cname ?? 'PKR';
@endphp

<div class="totals-wrap avoid-break">
    <div class="totals-block">
        <div class="totals-block__row grand">
            <span class="lbl">GRAND TOTAL ({{ $currencyName }})</span>
            <span class="val">{{ number_format($total, 2) }}</span>
        </div>
    </div>
</div>

@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <span class="amount-words-label">Amount in Words:</span>
    {{ numberToWordsWithCurrency($total) }} {{ $currencyName }}
</div>
@endif
@endif

{{-- ── Notes / Description ──────────────────────────────────────────────────── --}}
@if(!empty($order['description']))
<div class="avoid-break" style="margin-top:12px;">
    <div class="section-head">Description / Notes</div>
    <div class="note-box">{{ strip_tags($order['description']) }}</div>
</div>
@endif

@endsection

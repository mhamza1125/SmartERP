@extends('print.layout')

@section('title', 'Proforma_Invoice_' . ($order['order_no'] ?? 'N/A') . '_' . formatDate($order['order_date'] ?? now()))
@section('doc_title', 'Proforma Invoice')
@section('doc_no', 'PI-' . ($order['order_id'] ?? ''))

@push('styles')
<style>
</style>
@endpush

@section('content')
@php
    $totalQuantity = $orderItem->sum('quantity');
    $totalOriginal = $orderItem->sum(fn ($item) => $item->quantity * ($item->price ?? 0));
    $currencyName = $orderItem->first()->cname ?? 'PKR';
    $totalLabel = !empty($sellingType) ? 'Total ' . $sellingType . ' Amount:' : 'Grand Total:';
@endphp

{{-- Proforma invoice details --}}
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">P-Invoice No</span>
        <span class="meta-value mono">PI-{{ $order['order_id'] ?? '' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">P-Invoice Date</span>
        <span class="meta-value mono">{{ formatDate($order['order_date'] ?? null, 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Order No</span>
        <span class="meta-value mono">{{ $order['order_no'] ?? 'N/A' }}</span>
    </div>
    @if(!empty($order['due_date']))
    <div class="meta-field">
        <span class="meta-label">Delivery Date</span>
        <span class="meta-value mono">{{ formatDate($order['due_date']) }}</span>
    </div>
    @endif
    <div class="meta-field">
        <span class="meta-label">Total QTY</span>
        <span class="meta-value mono">{{ number_format($totalQuantity) }}</span>
    </div>
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
    @if(!empty($order['payment_terms']))
    <div class="meta-field">
        <span class="meta-label">Payment Terms</span>
        <span class="meta-value">{{ $order['payment_terms'] }}</span>
    </div>
    @endif
</div>

{{-- Customer --}}
<div class="party-block party-block--spaced">
    <div class="party-label">CUSTOMER</div>
    <div class="party-name">{{ $order['fname'] ?? 'N/A' }} {{ $order['lname'] ?? '' }}</div>
    @if(!empty($order['address']))
        <div class="party-detail">{{ $order['address'] }}</div>
    @endif
</div>

{{-- Order Items Table --}}
@if($orderItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">Sr.</th>
                <th class="text-center" style="width: 11%">Article No</th>
                <th class="text-left" style="width: 26%">Product</th>
                <th class="text-center" style="width: 18%">Size</th>
                <th class="num" style="width: 10%">QTY</th>
                <th class="num" style="width: 13%">Price</th>
                <th class="num" style="width: 17%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItem as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->article_no }}</td>
                <td class="text-left">{{ $item->pname }}</td>
                <td class="text-center">{{ $item->name }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="num">{{ number_format($item->quantity * ($item->price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total</td>
                <td class="num">{{ number_format($totalQuantity) }}</td>
                <td></td>
                <td class="num">{{ number_format($totalOriginal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Invoice Totals --}}
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>{{ $totalLabel }}
            @if(function_exists('numberToWordsWithCurrency'))
                {{ numberToWordsWithCurrency($totalOriginal) }} {{ $currencyName }}
            @endif
        </span>
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>

@include('print.partials.bank-details')

{{-- Certification Statement --}}
<div class="certification-statement avoid-break">
    <p>CERTIFIED TO BE TRUE AND CORRECT @if(!empty($company->name))&ldquo;{{ strtoupper($company->name) }}&rdquo;@endif</p>
</div>

@endsection

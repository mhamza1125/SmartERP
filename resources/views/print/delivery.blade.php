@extends('print.layout')

@section('title', 'Delivery_Details_' . ($delivery['delivery_no'] ?? 'N/A') . '_' . formatDate($delivery['delivery_date'] ?? now()))
@section('doc_title', 'Delivery Note')
@section('doc_no', $delivery['delivery_no'] ?? '')

@section('content')

{{-- ── Meta band: two rows of four ────────────────────────────────────────── --}}
<div class="meta-band meta-band--grid meta-band--cols-4">
    <div class="meta-field">
        <span class="meta-label">Delivery No</span>
        <span class="meta-value mono">{{ $delivery['delivery_no'] ?? 'N/A' }}</span>
    </div>
    @if(!empty($delivery['delivery_date']))
    <div class="meta-field">
        <span class="meta-label">Delivery Date</span>
        <span class="meta-value mono">{{ \Carbon\Carbon::parse($delivery['delivery_date'])->format('d-m-Y') }}</span>
    </div>
    @endif
    <div class="meta-field">
        <span class="meta-label">Order Date</span>
        <span class="meta-value mono">
            {{ !empty($delivery['order_date']) ? \Carbon\Carbon::parse($delivery['order_date'])->format('d-m-Y') : 'N/A' }}
        </span>
    </div>
    @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
    <div class="meta-field">
        <span class="meta-label">Order No(s)</span>
        <span class="meta-value mono">{{ collect($relatedOrders)->pluck('order_no')->filter()->implode(', ') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No(s)</span>
        <span class="meta-value mono">{{ collect($relatedOrders)->pluck('job_no')->filter()->implode(', ') }}</span>
    </div>
    @else
    <div class="meta-field">
        <span class="meta-label">Order No</span>
        <span class="meta-value mono">{{ $delivery['order_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $delivery['job_no'] ?? 'N/A' }}</span>
    </div>
    @endif
    @if(!empty($packingListInfo['total_cartons']))
    <div class="meta-field">
        <span class="meta-label">Cartons</span>
        <span class="meta-value mono">{{ number_format($packingListInfo['total_cartons']) }}</span>
    </div>
    @endif
    <div class="meta-field">
        <span class="meta-label">Method</span>
        <span class="meta-value">
            @if($delivery['delivery_method'] == 1) Sea Freight
            @elseif($delivery['delivery_method'] == 2) Air Freight
            @elseif($delivery['delivery_method'] == 3) Road Transport
            @else N/A @endif
        </span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Status</span>
        <span class="meta-value">
            @php
                $dStatusMap  = [1 => 'Pending', 2 => 'Dispatched', 3 => 'Delivered', 4 => 'Returned'];
                $dPillMap    = [1 => 'pill-warning', 2 => 'pill-info', 3 => 'pill-success', 4 => 'pill-danger'];
                $ds = $delivery['delivery_status'] ?? 0;
            @endphp
            <span class="status-pill {{ $dPillMap[$ds] ?? 'pill-secondary' }}">{{ $dStatusMap[$ds] ?? 'Unknown' }}</span>
        </span>
    </div>
</div>

{{-- ── Shipper ──────────────────────────────────────────────────────────────── --}}
@if(!empty($delivery['fshipping']) || !empty($delivery['fport_no']))
<div class="party-block" style="margin-bottom:12px;">
    <div class="party-label">SHIPPED FROM</div>
    @if(!empty($delivery['fshipping']))
        <div class="party-detail">{{ $delivery['fshipping'] }}</div>
    @endif
    @if(!empty($delivery['fport_no']))
        <div class="party-contact">Port: {{ $delivery['fport_no'] }}</div>
    @endif
</div>
@endif

{{-- ── Statement of Origin ─────────────────────────────────────────────────── --}}
@if(!empty($delivery['so_origin']))
<div class="avoid-break" style="margin-bottom:10px;">
    <div class="section-head">Statement of Origin</div>
    <div class="note-box">{!! nl2br(e(strip_tags($delivery['so_origin']))) !!}</div>
</div>
@endif

{{-- ── Delivery Description ─────────────────────────────────────────────────── --}}
@if(!empty($delivery['description']))
<div class="avoid-break" style="margin-bottom:10px;">
    <div class="section-head">Delivery Details</div>
    <div class="note-box">{!! nl2br(e(strip_tags($delivery['description']))) !!}</div>
</div>
@endif

{{-- ── Delivery Items ───────────────────────────────────────────────────────── --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <div class="table-title">Delivered Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:6%" class="text-center">Sr.</th>
                <th style="width:13%" class="code">Article No</th>
                <th class="text-left">Product</th>
                <th style="width:13%" class="text-center">Size</th>
                <th style="width:12%" class="num">QTY</th>
                <th style="width:10%" class="num">Boxes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryItem as $item)
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td class="code">{{ $item->article_no }}</td>
                <td>{{ $item->name }}</td>
                <td class="text-center">{{ $item->hname ?? 'N/A' }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format(ceil($item->quantity * ($item->bqty ?? 1))) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total</td>
                <td class="num">{{ number_format($deliveryItem->sum('quantity') ?? 0) }}</td>
                <td class="num">{{ number_format($deliveryItem->sum(function($item) { return ceil($item->quantity * ($item->bqty ?? 1)); }) ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@endif

{{-- ── Delivery Instructions ────────────────────────────────────────────────── --}}
@if(!empty($delivery['instructions']))
<div class="avoid-break" style="margin-top:10px;">
    <div class="section-head">Delivery Instructions</div>
    <div class="note-box">{{ $delivery['instructions'] }}</div>
</div>
@endif

{{-- ── Signatures ───────────────────────────────────────────────────────────── --}}
<div class="signatures avoid-break">
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Signature</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Delivery Person</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Authorized Signatory</div>
    </div>
</div>

@endsection

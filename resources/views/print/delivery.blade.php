@extends('print.layout')

@section('title', 'Delivery_Details_' . ($delivery['delivery_no'] ?? 'N/A') . '_' . ($delivery['delivery_date'] ?? date('d-m-Y')))

@section('content')

<div class="doc-title-row">
    <span class="doc-title-label">DELIVERY NOTE</span>
    <span class="doc-title-no"># {{ $delivery['delivery_no'] ?? 'N/A' }}</span>
</div>

{{-- ── Meta band ──────────────────────────────────────────────────────────── --}}
<div class="meta-band">
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

{{-- ── Parties ──────────────────────────────────────────────────────────────── --}}
<div class="party-grid">
    {{-- Shipper (our company) --}}
    <div class="party-block">
        <div class="party-label">SHIPPER / FROM</div>
        <div class="party-name">{{ $company->name ?? '' }}</div>
        @if(!empty($delivery['fshipping']))
            <div class="party-detail">{{ $delivery['fshipping'] }}</div>
        @endif
        @if(!empty($delivery['fport_no']))
            <div class="party-contact">Port: {{ $delivery['fport_no'] }}</div>
        @endif
        @if(!empty($company->ntn))
            <div class="party-detail">NTN: {{ $company->ntn }}</div>
        @endif
        @if(!empty($company->rex_no))
            <div class="party-detail">REX: {{ $company->rex_no }}</div>
        @endif
    </div>

    {{-- Consignee --}}
    <div class="party-block">
        <div class="party-label">CONSIGNEE / TO</div>
        <div class="party-name">{{ $delivery['fname'] ?? ($delivery['customer_no'] ?? 'N/A') }}</div>
        @if(!empty($delivery['tshipping']))
            <div class="party-detail">{{ $delivery['tshipping'] }}</div>
        @endif
        @if(!empty($delivery['tport_no']))
            <div class="party-contact">Port: {{ $delivery['tport_no'] }}</div>
        @endif
        @if(!empty($delivery['fi_no']))
            <div class="party-detail">FI: {{ $delivery['fi_no'] }}</div>
        @endif
    </div>
</div>

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
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:6%">Sr.</th>
                <th style="width:13%" class="text-center">Article No</th>
                <th>Product Name</th>
                <th style="width:13%" class="text-center">Size</th>
                <th style="width:12%" class="text-right">Qty Delivered</th>
                <th style="width:10%" class="text-right">Boxes</th>
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
                <td colspan="4" class="text-right">Total:</td>
                <td class="num">{{ number_format($deliveryItem->sum('quantity') ?? 0) }}</td>
                <td class="num">{{ number_format($deliveryItem->sum(function($item) { return ceil($item->quantity * ($item->bqty ?? 1)); }) ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Summary tiles --}}
<div class="totals-wrap avoid-break">
    <div class="totals-block">
        <div class="totals-block__row">
            <span class="lbl">Total Items Delivered</span>
            <span class="val">{{ number_format($deliveryItem->sum('quantity') ?? 0) }}</span>
        </div>
        <div class="totals-block__row">
            <span class="lbl">Total Boxes</span>
            <span class="val">{{ number_format($deliveryItem->sum(function($item) { return ceil($item->quantity * ($item->bqty ?? 1)); }) ?? 0) }}</span>
        </div>
        @if(isset($packingListInfo) && $packingListInfo)
        <div class="totals-block__row">
            <span class="lbl">Total Cartons</span>
            <span class="val">{{ number_format($packingListInfo['total_cartons']) }}</span>
        </div>
        @endif
        @if(isset($delivery['delivery_charges']) && $delivery['delivery_charges'] > 0)
        <div class="totals-block__row">
            <span class="lbl">Delivery Charges</span>
            <span class="val">{{ number_format($delivery['delivery_charges'], 2) }}</span>
        </div>
        @endif
    </div>
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
        <div class="signature-label">Customer Signature</div>
        <div class="signature-label" style="margin-top:4px;">Date: ___________</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Delivery Person</div>
        <div class="signature-label" style="margin-top:4px;">Name: ___________</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Authorized Signatory</div>
        <div class="signature-label" style="margin-top:4px;">Company Seal</div>
    </div>
</div>

@endsection

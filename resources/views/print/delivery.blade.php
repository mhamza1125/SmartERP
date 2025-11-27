@extends('print.layout')

@section('title', 'Delivery_Challan_' . ($delivery['delivery_no'] ?? 'N/A') . '_' . ($delivery['delivery_date'] ?? date('Y-m-d')))

@section('content')
<div class="document-title">Delivery Challan</div>

{{-- Delivery Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer No:</span>
            <span class="info-value">{{ $delivery['customer_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
        <div class="info-row">
            <span class="info-label">Primary Order:</span>
            <span class="info-value">{{ $delivery['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Primary Job No:</span>
            <span class="info-value">{{ $delivery['job_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">All Orders:</span>
            <span class="info-value">
                @foreach($relatedOrders as $index => $order)
                    @if($index < 5)
                    <span class="badge badge-secondary mr-1">{{ $order->order_no ?? 'N/A' }}</span>
                    @endif
                @endforeach
                @if(count($relatedOrders) > 5)
                <span class="badge badge-light">+{{ count($relatedOrders) - 5 }} more</span>
                @endif
            </span>
        </div>
        @else
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $delivery['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $delivery['job_no'] ?? 'N/A' }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{ $delivery['order_date'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Delivery No:</span>
            <span class="info-value">{{ $delivery['customer_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Shipping From:</span>
            <span class="info-value">{{ $delivery['fshipping'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Port No:</span>
            <span class="info-value">{{ $delivery['fport_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Shipping To:</span>
            <span class="info-value">{{ $delivery['tshipping'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Port No:</span>
            <span class="info-value">{{ $delivery['tport_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Delivery Method:</span>
            <span class="info-value">
                @if($delivery['delivery_method'] == 1) Sea Freight
                @elseif($delivery['delivery_method'] == 2) Air Freight
                @elseif($delivery['delivery_method'] == 3) Road Transport
                @else Unknown @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Delivery Status:</span>
            <span class="info-value">
                @if($delivery['delivery_status'] == 1) <span class="badge badge-warning">Pending</span>
                @elseif($delivery['delivery_status'] == 2) <span class="badge badge-success">Delivered</span>
                @elseif($delivery['delivery_status'] == 3) <span class="badge badge-danger">Returned</span>
                @elseif($delivery['delivery_status'] == 4) <span class="badge badge-danger">Disputed</span>
                @else <span class="badge badge-secondary">Unknown</span> @endif
            </span>
        </div>
        @if(isset($delivery['fi_no']) && !empty($delivery['fi_no']))
        <div class="info-row">
            <span class="info-label">FI No:</span>
            <span class="info-value">{{ $delivery['fi_no'] }}</span>
        </div>
        @endif
        @if(isset($delivery['rex_no']) && !empty($delivery['rex_no']))
        <div class="info-row">
            <span class="info-label">REX No:</span>
            <span class="info-value">{{ $delivery['rex_no'] }}</span>
        </div>
        @endif
        @if(isset($delivery['ntn']) && !empty($delivery['ntn']))
        <div class="info-row">
            <span class="info-label">NTN:</span>
            <span class="info-value">{{ $delivery['ntn'] }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Statement of Origin --}}
@if(isset($delivery['so_origin']) && !empty($delivery['so_origin']))
<div class="info-section avoid-break">
    <h3>Statement of Origin</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($delivery['so_origin'])) !!}
    </div>
</div>
@endif

{{-- Delivery Description --}}
@if(isset($delivery['description']) && !empty($delivery['description']))
<div class="info-section avoid-break">
    <h3>Delivery Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($delivery['description'])) !!}
    </div>
</div>
@endif

{{-- Delivery Items Table --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <h3>Delivered Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 8%">Sr.</th>
                <th style="width: 30%">Product</th>
                <th style="width: 15%">Article No</th>
                <th style="width: 15%">Size</th>
                <th style="width: 12%">Delivered</th>
                <th style="width: 10%">Boxes</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryItem->count())
                @foreach($deliveryItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    <td>{{ $item->article_no }} - {{ $item->name }}</td>
                    <td class="text-center">{{ $item->article_no }}</td>
                    <td class="text-center">{{ $item->hname ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">{{ number_format(ceil($item->quantity * ($item->bqty ?? 1))) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($deliveryItem->sum('quantity') ?? 0) }}</td>
                <td class="text-right">{{ number_format($deliveryItem->sum(function($item) { return ceil($item->quantity * ($item->bqty ?? 1)); }) ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Delivery Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Items Delivered:</span>
        <span>{{ number_format($deliveryItem->sum('quantity') ?? 0) }}</span>
    </div>
    <div class="total-row">
        <span>Total Boxes:</span>
        <span>{{ number_format($deliveryItem->sum(function($item) { return ceil($item->quantity * ($item->bqty ?? 1)); }) ?? 0) }}</span>
    </div>
    @if(isset($delivery['delivery_charges']) && $delivery['delivery_charges'] > 0)
    <div class="total-row">
        <span>Delivery Charges:</span>
        <span class="amount">{{ number_format($delivery['delivery_charges'], 2) }}</span>
    </div>
    @endif
</div>

{{-- Delivery Instructions --}}
@if(isset($delivery['instructions']) && !empty($delivery['instructions']))
<div class="info-section avoid-break">
    <h3>Delivery Instructions</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $delivery['instructions'] }}
    </div>
</div>
@endif

{{-- Terms and Conditions --}}
<div class="info-section avoid-break">
    <h3>Terms and Conditions</h3>
    <div style="font-size: 10px; line-height: 1.3;">
        <ul style="margin: 0; padding-left: 15px;">
            <li>Goods once delivered will not be taken back without prior approval.</li>
            <li>Any damage or shortage must be reported within 24 hours of delivery.</li>
            <li>This delivery challan is subject to verification and final billing.</li>
            <li>Customer signature confirms receipt of goods in good condition.</li>
        </ul>
    </div>
</div>

{{-- Signatures --}}
<div class="signatures avoid-break">
    <div class="signature-box">
        <div class="signature-line"></div>
        <div>Customer Signature</div>
        <div style="font-size: 10px; margin-top: 5px;">Date: ___________</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div>Delivery Person</div>
        <div style="font-size: 10px; margin-top: 5px;">Name: ___________</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div>Authorized Signatory</div>
        <div style="font-size: 10px; margin-top: 5px;">Company Seal</div>
    </div>
</div>
@endsection

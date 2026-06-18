@extends('print.layout')

@section('title', 'Delivery_Details_' . ($delivery['delivery_no'] ?? 'N/A') . '_' . ($delivery['delivery_date'] ?? date('d-m-Y')))

@section('content')
<div class="document-title">Delivery Details</div>

{{-- Delivery Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer No:</span>
            <span class="info-value">{{ $delivery['customer_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
        <div class="info-row">
            <span class="info-label">Order No(s):</span>
            <span class="info-value">{{ collect($relatedOrders)->pluck('order_no')->filter()->implode(', ') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No(s):</span>
            <span class="info-value">{{ collect($relatedOrders)->pluck('job_no')->filter()->implode(', ') }}</span>
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
            <span class="info-value">{{ !empty($delivery['order_date']) ? \Carbon\Carbon::parse($delivery['order_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        @if(isset($delivery['fi_no']) && !empty($delivery['fi_no']))
        <div class="info-row">
            <span class="info-label">FI No:</span>
            <span class="info-value">{{ $delivery['fi_no'] }}</span>
        </div>
        @endif
        @if(isset($company) && !empty($company->rex_no))
        <div class="info-row">
            <span class="info-label">REX No:</span>
            <span class="info-value">{{ $company->rex_no }}</span>
        </div>
        @endif
        @if(isset($company) && !empty($company->ntn))
        <div class="info-row">
            <span class="info-label">NTN:</span>
            <span class="info-value">{{ $company->ntn }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Delivery No:</span>
            <span class="info-value">{{ $delivery['delivery_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($delivery['delivery_date']) && !empty($delivery['delivery_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($delivery['delivery_date'])->format('d-m-Y') }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Shipping From:</span>
            <span class="info-value">{{ $delivery['fshipping'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Origin Port:</span>
            <span class="info-value">{{ $delivery['fport_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Shipping To:</span>
            <span class="info-value">{{ $delivery['tshipping'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Destination Port:</span>
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
                @if($delivery['delivery_status'] == 1) Pending
                @elseif($delivery['delivery_status'] == 2) Dispatched
                @elseif($delivery['delivery_status'] == 3) Delivered
                @elseif($delivery['delivery_status'] == 4) Returned
                @else Unknown @endif
            </span>
        </div>
    </div>
</div>

{{-- Statement of Origin --}}
@if(isset($delivery['so_origin']) && !empty($delivery['so_origin']))
<div class="info-section avoid-break">
    <h3>Statement of Origin</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($delivery['so_origin']))) !!}
    </div>
</div>
@endif

{{-- Delivery Description --}}
@if(isset($delivery['description']) && !empty($delivery['description']))
<div class="info-section avoid-break">
    <h3>Delivery Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($delivery['description']))) !!}
    </div>
</div>
@endif

{{-- Delivery Items Table --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 8%">Sr.</th>
                <th style="width: 15%">Article No</th>
                <th style="width: 30%">Product Name</th>
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
                    <td class="text-center">{{ $item->article_no }}</td>
                    <td class="text-left">{{ $item->name }}</td>
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
    @if(isset($packingListInfo) && $packingListInfo)
    <div class="total-row">
        <span>Total Cartons (Packing List):</span>
        <span>{{ number_format($packingListInfo['total_cartons']) }}</span>
    </div>
    @endif
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

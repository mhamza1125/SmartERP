@extends('print.layout')

@section('title', 'Receive_' . ($receive['receive_no'] ?? 'N/A') . '_' . formatDate($receive['receive_date'] ?? now()))
@section('doc_title', 'Goods Received')
@section('doc_no', $receive['receive_no'] ?? '')

@section('content')

{{-- Receive Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Receive No:</span>
            <span class="info-value">{{ $receive['receive_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">P.O.#:</span>
            <span class="info-value">{{ $receive['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $receive['job_no'] ?? 'Default Purchase' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Required Date:</span>
            <span class="info-value">{{ formatDate($receive['require_date'] ?? null, 'N/A') }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor:</span>
            <span class="info-value">{{ $receive['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $receive['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $receive['address'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Receive Date:</span>
            <span class="info-value">{{ !empty($receive['receive_date']) ? \Carbon\Carbon::parse($receive['receive_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @php
                    $statusText = 'Pending';
                    if(isset($receive['stock_status'])) {
                        if($receive['stock_status'] == 1) {
                            $statusText = 'Completely Received';
                        } elseif($receive['stock_status'] == 2) {
                            $statusText = 'Partially Received';
                        }
                    }
                @endphp
                {{ $statusText }}
            </span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($receive['desc']) && !empty($receive['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($receive['desc']))) !!}
    </div>
</div>
@endif

{{-- Received Items (materials or products) --}}
@if(isset($receiveMaterial) && $receiveMaterial->count() > 0)
@php $isMaterial = ($receive['purchase_type'] ?? '') == 'material'; @endphp
<div class="items-section avoid-break">
    <div class="table-title">Received Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 6%">Sr.</th>
                <th class="text-center" style="width: 15%">{{ $isMaterial ? 'Material No' : 'Article No' }}</th>
                <th class="text-left">{{ $isMaterial ? 'Material Name' : 'Product Name' }}</th>
                <th class="text-center" style="width: 14%">{{ $isMaterial ? 'Unit' : 'Size' }}</th>
                <th class="num" style="width: 14%">QTY</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receiveMaterial as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $isMaterial ? ($item->material_no ?? '') : ($item->article_no ?? '') }}</td>
                <td class="text-left">{{ $item->name ?? '' }}</td>
                <td class="text-center">{{ $item->hname ?? '' }}</td>
                <td class="num">{{ number_format($item->quantity ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total</td>
                <td class="num">{{ number_format($receiveMaterial->sum('quantity'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

<div class="sign-row sign-row--single">
    <span class="sign-row__field">Received By:<span class="sign-row__line"></span></span>
</div>

@endsection

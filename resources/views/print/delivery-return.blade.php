@extends('print.layout')

@section('title', 'Delivery_Return_' . ($return['return_no'] ?? 'N/A') . '_' . ($return['return_date'] ?? date('d-m-Y')))

@push('styles')
<style>
    .document-info  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .info-section   { border: 1px solid #ddd; padding: 12px; }
    .info-value     { flex: 1; text-align: right; }
    .document-section { margin-bottom: 20px; }
    .document-section h3 { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 4px; }
    .summary-info   { border: 1px solid #ddd; padding: 12px; }
    .summary-row    { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 11px; }
    .summary-label  { font-weight: bold; }
    .summary-value  { text-align: right; }
    .alert          { padding: 10px; margin-bottom: 16px; border: 1px solid #ddd; }
    .alert-warning  { background-color: #fff3cd; border-color: #ffc107; color: #856404; }
</style>
@endpush

@section('content')
<div class="document-title">Delivery Return</div>

{{-- Return Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Return No:</span>
            <span class="info-value">{{ $return['return_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Return Date:</span>
            <span class="info-value">{{ !empty($return['return_date']) ? \Carbon\Carbon::parse($return['return_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Return Reason:</span>
            <span class="info-value">{{ $return['return_reason'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ $return['return_description'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $return['order_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $return['job_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value">{{ $return['fname'] ?? '' }} {{ $return['lname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stock No:</span>
            <span class="info-value">{{ $return['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Original Delivery Date:</span>
            <span class="info-value">{{ !empty($return['stock_date']) ? \Carbon\Carbon::parse($return['stock_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Returned Items Table --}}
@if($returnItems && $returnItems->count() > 0)
<div class="document-section">
    <h3>Returned Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 20%">Item</th>
                <th style="width: 15%">Article No</th>
                <th style="width: 10%">Size</th>
                <th style="width: 10%">Stage</th>
                <th style="width: 10%">Original Qty</th>
                <th style="width: 10%">Returned Qty</th>
                <th style="width: 10%">Unit</th>
                <th style="width: 10%">Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnItems as $item)
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td>
                    @if($item->product_name)
                        {{ $item->product_name }}
                    @else
                        {{ $item->material_name }}
                    @endif
                </td>
                <td>{{ $item->article_no ?? 'N/A' }}</td>
                <td>{{ $item->size_name ?? 'N/A' }}</td>
                <td>{{ $item->stage_name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->original_quantity) }}</td>
                <td class="text-right">{{ number_format($item->quantity) }}</td>
                <td>{{ $item->product_unit ?? $item->material_unit ?? 'N/A' }}</td>
                <td>{{ $item->reason ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Summary Section --}}
<div class="document-section">
    <h3>Return Summary</h3>
    <div class="summary-info">
        <div class="summary-row">
            <span class="summary-label">Total Items Returned:</span>
            <span class="summary-value">{{ $returnItems->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Quantity Returned:</span>
            <span class="summary-value">{{ number_format($returnItems->sum('quantity')) }}</span>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <h5>No Items Returned</h5>
    <p>No items have been returned for this delivery return record.</p>
</div>
@endif

@endsection


@extends('print.layout')

@section('title', 'Product_Cost_' . ($product['article_no'] ?? 'N/A') . '_' . ($product['name'] ?? 'N/A'))
@section('doc_title', 'Product Costing & Wages')
@section('doc_no', $product['article_no'] ?? '')

@push('styles')
<style>
    .document-section { margin-bottom: 20px; }
    .document-section h3 { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 4px; }
    .alert          { padding: 10px; margin-bottom: 16px; border: 1px solid #ddd; }
    .alert-warning  { background-color: #fff3cd; border-color: #ffc107; color: #856404; }
</style>
@endpush

@section('content')

{{-- Product Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Article No</span>
            <span class="meta-value">{{ $product['article_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Product Name</span>
            <span class="meta-value">{{ $product['name'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Category</span>
            <span class="meta-value">{{ $product['cname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Unit</span>
            <span class="meta-value">{{ $product['hname'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

@php
    // General cost rows make up the product cost; employee / vendor rows are labor-specific wages
    $generalCosts = $productCost->filter(fn ($item) => $item->table_name === 'general');
    $laborCosts = $productCost->reject(fn ($item) => $item->table_name === 'general');
@endphp

@if($productCost->count() > 0)
<div class="document-section">
    <div class="table-title">General Cost</div>
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%">Sr.</th>
                <th class="text-left">Cost Head</th>
                <th class="num" style="width: 22%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($generalCosts as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">{{ $item->hname ?? 'N/A' }}</td>
                <td class="num">{{ number_format($item->amount ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center">No general cost recorded</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">Total Cost</td>
                <td class="num">{{ number_format($generalCosts->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@if($laborCosts->count() > 0)
<div class="document-section">
    <div class="table-title">Labor-Specific Cost (Wages)</div>
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%">Sr.</th>
                <th class="text-left" style="width: 40%">Employee / Vendor</th>
                <th class="text-left">Stage / Cost Head</th>
                <th class="num" style="width: 22%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laborCosts as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">{{ $item->employee_no ?? $item->vendor_no }}{{ $item->name ? ' - ' . $item->name : '' }}</td>
                <td class="text-left">{{ $item->hname ?? 'N/A' }}</td>
                <td class="num">{{ number_format($item->amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="font-size: 9.5px; color: var(--muted);">Labor-specific wages are listed for reference and are not included in the Total Cost.</p>
</div>
@endif
@else
<div class="alert alert-warning">
    <h5>No Cost Data</h5>
    <p>No cost information has been recorded for this product.</p>
</div>
@endif

@endsection


@extends('print.layout')

@section('title', 'Material_Info_' . ($material['material_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Material Information')
@section('doc_no', $material['material_no'] ?? '')

@section('content')
{{-- Material Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Material No</span>
            <span class="meta-value">{{ $material['material_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Material Name</span>
            <span class="meta-value">{{ $material['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Material Type</span>
            <span class="meta-value">{{ $material['mtname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Unit</span>
            <span class="meta-value">{{ $material['uname'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Current Price</span>
            <span class="meta-value">{{ isset($material['cprice']) ? number_format($material['cprice'], 2) : 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Current Stock</span>
            <span class="meta-value">{{ number_format($currentStock ?? 0) }} {{ $material['uname'] ?? '' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Opening Stock</span>
            <span class="meta-value">{{ number_format($material['quantity'] ?? 0) }} {{ $material['uname'] ?? '' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Location</span>
            <span class="meta-value">{{ $material['location'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Status</span>
            <span class="meta-value">{{ $material['status'] ?? 'Active' }}</span>
        </div>
    </div>
</div>

{{-- Vendor Information --}}
<div class="info-section avoid-break">
    <h3>Vendor Information</h3>
    <div class="meta-band meta-band--grid">
        <div class="meta-group">
            <div class="meta-field">
                <span class="meta-label">Vendor No</span>
                <span class="meta-value">{{ $material['vendor_no'] ?? 'N/A' }}</span>
            </div>
            <div class="meta-field">
                <span class="meta-label">Vendor Name</span>
                <span class="meta-value">{{ $material['fname'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($material['description']) && !empty($material['description']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($material['description']))) !!}
    </div>
</div>
@endif

{{-- Material Images --}}
@if(isset($image) && $image->count() > 0)
<div class="avoid-break">
    <h3>Material Images</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach($image as $img)
        <div style="border: 1px solid #ddd; padding: 5px; text-align: center;">
            <img src="{{ asset('storage/materials/' . $img->image_name) }}" 
                 alt="Material Image" 
                 style="max-width: 150px; max-height: 150px; object-fit: cover;"
                 onerror="this.style.display='none'">
            <div style="font-size: 10px; margin-top: 5px;">{{ $img->image_name }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Current Stock Value:</span>
        <span>{{ isset($material['cprice']) ? number_format(($currentStock ?? 0) * $material['cprice'], 2) : 'N/A' }}</span>
    </div>
    <div class="total-row">
        <span>Material Status:</span>
        <span>{{ $material['status'] ?? 'Active' }}</span>
    </div>
</div>

@endsection

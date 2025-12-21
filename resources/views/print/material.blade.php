@extends('print.layout')

@section('title', 'Material_Info_' . ($material['material_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Material Information</div>

{{-- Material Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Material No:</span>
            <span class="info-value">{{ $material['material_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Material Name:</span>
            <span class="info-value">{{ $material['name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Material Type:</span>
            <span class="info-value">{{ $material['mtname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Unit:</span>
            <span class="info-value">{{ $material['uname'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Current Price:</span>
            <span class="info-value">{{ isset($material['cprice']) ? number_format($material['cprice'], 2) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Stock:</span>
            <span class="info-value">{{ isset($material['quantity']) ? number_format($material['quantity']) : '0' }} {{ $material['uname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Location:</span>
            <span class="info-value">{{ $material['location'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">{{ $material['status'] ?? 'Active' }}</span>
        </div>
    </div>
</div>

{{-- Vendor Information --}}
<div class="info-section avoid-break">
    <h3>Vendor Information</h3>
    <div class="document-info">
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Vendor No:</span>
                <span class="info-value">{{ $material['vendor_no'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vendor Name:</span>
                <span class="info-value">{{ $material['fname'] ?? 'N/A' }}</span>
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
        <span>{{ isset($material['quantity']) && isset($material['cprice']) ? number_format($material['quantity'] * $material['cprice'], 2) : 'N/A' }}</span>
    </div>
    <div class="total-row">
        <span>Material Status:</span>
        <span>{{ $material['status'] ?? 'Active' }}</span>
    </div>
</div>

@endsection

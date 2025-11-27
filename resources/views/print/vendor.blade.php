@extends('print.layout')

@section('title', 'Vendor_Info_' . ($vendor['vendor_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Vendor Information</div>

{{-- Vendor Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor No:</span>
            <span class="info-value">{{ $vendor['vendor_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value">{{ $vendor['name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Full Name:</span>
            <span class="info-value">{{ $vendor['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor Type:</span>
            <span class="info-value">{{ $vendor['vtname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">City:</span>
            <span class="info-value">{{ $vendor['cname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact No:</span>
            <span class="info-value">{{ $vendor['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Person:</span>
            <span class="info-value">{{ $vendor['cperson'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone No:</span>
            <span class="info-value">{{ $vendor['phone2'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Address Information --}}
<div class="info-section avoid-break">
    <h3>Address</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $vendor['address'] ?? 'N/A' }}, {{ $vendor['cname'] ?? '' }}
    </div>
</div>

{{-- Vendor Description --}}
@if(isset($vendor['description']) && !empty($vendor['description']))
<div class="info-section avoid-break">
    <h3>Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($vendor['description'])) !!}
    </div>
</div>
@endif

@endsection

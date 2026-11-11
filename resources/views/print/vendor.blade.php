@extends('print.layout')

@section('title', 'Vendor_Info_' . ($vendor['vendor_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Vendor Information')
@section('doc_no', $vendor['vendor_no'] ?? '')

@section('content')
{{-- Vendor Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Vendor No</span>
            <span class="meta-value">{{ $vendor['vendor_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Name</span>
            <span class="meta-value">{{ $vendor['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Full Name</span>
            <span class="meta-value">{{ $vendor['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Vendor Type</span>
            <span class="meta-value">{{ $vendor['vtname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">City</span>
            <span class="meta-value">{{ $vendor['cname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Contact No</span>
            <span class="meta-value">{{ $vendor['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Contact Person</span>
            <span class="meta-value">{{ $vendor['cperson'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Phone No</span>
            <span class="meta-value">{{ $vendor['phone2'] ?? 'N/A' }}</span>
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
        {!! nl2br(e(strip_tags($vendor['description']))) !!}
    </div>
</div>
@endif

@endsection

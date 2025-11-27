@extends('print.layout')

@section('title', 'Machine_Info_' . ($machine['machine_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Machine Information</div>

{{-- Machine Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Machine No:</span>
            <span class="info-value">{{ $machine['machine_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Machine Type:</span>
            <span class="info-value">{{ $machine['machine_type'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Location:</span>
            <span class="info-value">{{ $machine['location'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Employee:</span>
            <span class="info-value">{{ $machine['employee_name'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($machine['description']) && !empty($machine['description']))
<div class="info-section avoid-break">
    <h3>Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($machine['description'])) !!}
    </div>
</div>
@endif

@endsection

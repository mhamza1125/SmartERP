@extends('print.layout')

@section('title', 'Machine_Info_' . ($machine['machine_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Machine Information')
@section('doc_no', $machine['machine_no'] ?? '')

@section('content')
{{-- Machine Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Machine No</span>
            <span class="meta-value">{{ $machine['machine_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Machine Type</span>
            <span class="meta-value">{{ $machine['machine_type'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Location</span>
            <span class="meta-value">{{ $machine['location'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Employee</span>
            <span class="meta-value">{{ $machine['employee_name'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($machine['description']) && !empty($machine['description']))
<div class="info-section avoid-break">
    <h3>Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($machine['description']))) !!}
    </div>
</div>
@endif

@endsection

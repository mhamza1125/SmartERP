@extends('print.layout')

@section('title', 'Contractor_Info_' . ($vendor['vendor_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Contractor Information</div>

{{-- Contractor Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Contractor No:</span>
            <span class="info-value">{{ $vendor['vendor_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contractor Name:</span>
            <span class="info-value">{{ $vendor['fname'] ?? 'N/A' }} {{ $vendor['lname'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Company:</span>
            <span class="info-value">{{ $vendor['company'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">City:</span>
            <span class="info-value">{{ $vendor['cname'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $vendor['email'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone 1:</span>
            <span class="info-value">{{ $vendor['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone 2:</span>
            <span class="info-value">{{ $vendor['phone2'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @if(isset($vendor['status']) && $vendor['status'])
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Address Information --}}
<div class="info-section avoid-break">
    <h3>Address Information</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {{ $vendor['address'] ?? 'N/A' }}@if(isset($vendor['cname']) && !empty($vendor['cname']))<br>{{ $vendor['cname'] }}@endif
    </div>
</div>

{{-- Contractor Description --}}
@if(isset($vendor['description']) && !empty($vendor['description']))
<div class="info-section avoid-break">
    <h3>Contractor Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($vendor['description'])) !!}
    </div>
</div>
@endif

{{-- Bank Information --}}
@if(isset($vendor['bank_name']) || isset($vendor['account_title']) || isset($vendor['account_no']))
<div class="info-section avoid-break">
    <h3>Banking Information</h3>
    <table class="print-table">
        <tbody>
            @if(isset($vendor['bank_name']) && !empty($vendor['bank_name']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Bank Name:</td>
                <td>{{ $vendor['bank_name'] }}</td>
            </tr>
            @endif
            @if(isset($vendor['account_title']) && !empty($vendor['account_title']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Account Title:</td>
                <td>{{ $vendor['account_title'] }}</td>
            </tr>
            @endif
            @if(isset($vendor['account_no']) && !empty($vendor['account_no']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Account Number:</td>
                <td>{{ $vendor['account_no'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Contact Person Information --}}
@if(isset($vendor['contact_person']) || isset($vendor['contact_phone']))
<div class="info-section avoid-break">
    <h3>Contact Person</h3>
    <table class="print-table">
        <tbody>
            @if(isset($vendor['contact_person']) && !empty($vendor['contact_person']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Contact Person:</td>
                <td>{{ $vendor['contact_person'] }}</td>
            </tr>
            @endif
            @if(isset($vendor['contact_phone']) && !empty($vendor['contact_phone']))
            <tr>
                <td style="width: 30%; font-weight: bold;">Contact Phone:</td>
                <td>{{ $vendor['contact_phone'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

@endsection


@extends('print.layout')

@section('title', 'Machine_Issuance_' . ($issue['stock_no'] ?? 'N/A') . '_' . date('d-m-Y'))

@section('content')
<div class="document-title">Machine Material Issuance</div>

{{-- Issuance Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Issuance No:</span>
            <span class="info-value">{{ $issue['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Issuance Date:</span>
            <span class="info-value">{{ $issue['stock_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Employee:</span>
            <span class="info-value">{{ $issue['employee_no'] ?? '' }} - {{ $issue['name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span class="info-value">{{ $issue['hname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Machine No:</span>
            <span class="info-value">{{ $issue['machine_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Machine Type:</span>
            <span class="info-value">{{ $issue['mname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Machine Location:</span>
            <span class="info-value">{{ $issue['location'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($issue['description']) && !empty($issue['description']))
<div class="info-section avoid-break">
    <h3>Details</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($issue['description']))) !!}
    </div>
</div>
@endif

{{-- Issued Materials --}}
@if(isset($issueItem) && $issueItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Issued Materials</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issueItem as $item)
            @if($item->quantity)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0, 2) }} {{ $item->hname ?? '' }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection


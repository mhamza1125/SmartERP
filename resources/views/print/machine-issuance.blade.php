@extends('print.layout')

@section('title', 'Machine_Issuance_' . ($issue['stock_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Machine Material Issuance')
@section('doc_no', $issue['stock_no'] ?? '')

@section('content')
{{-- Issuance Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Issuance No</span>
            <span class="meta-value">{{ $issue['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Issuance Date</span>
            <span class="meta-value">{{ !empty($issue['stock_date']) ? \Carbon\Carbon::parse($issue['stock_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Employee</span>
            <span class="meta-value">{{ $issue['employee_no'] ?? '' }} - {{ $issue['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Department</span>
            <span class="meta-value">{{ $issue['hname'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Machine No</span>
            <span class="meta-value">{{ $issue['machine_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Machine Type</span>
            <span class="meta-value">{{ $issue['mname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Machine Location</span>
            <span class="meta-value">{{ $issue['location'] ?? 'N/A' }}</span>
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
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material</th>
                <th>QTY</th>
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


@extends('print.layout')

@section('title', 'PTC_Template_' . ($product->article_no ?? 'N/A'))

@push('styles')
<style>
    .document-info  { display: flex; justify-content: space-between; margin-bottom: 20px; border: 1px solid #ddd; padding: 12px; background-color: #f9f9f9; }
    .info-section   { flex: 1; margin-right: 16px; }
    .info-section:last-child { margin-right: 0; }
    .document-section { margin-top: 20px; }
    .document-section h3 { font-size: 13px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 4px; }
    .print-table td { height: 28px; }
</style>
@endpush

@section('content')
<div class="document-title">Process Travel Card (PTC)</div>

{{-- Product Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Product Name:</span>
            <span class="info-value">{{ $product->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Article No:</span>
            <span class="info-value">{{ $product->article_no ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Size:</span>
            <span class="info-value">
                @if($size && count($size) > 0)
                    {{ collect($size)->pluck('name')->join(', ') }}
                @else
                    N/A
                @endif
            </span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Category:</span>
            <span class="info-value">{{ $product->cname ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Unit:</span>
            <span class="info-value">{{ $product->hname ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- PTC Process Table --}}
<div class="document-section">
    <h3>Production Processes</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 20%">Processes</th>
                <th style="width: 12%">Starting Date</th>
                <th style="width: 8%">Qty</th>
                <th style="width: 8%">Re-Work</th>
                <th style="width: 8%">Rej</th>
                <th style="width: 12%">Pass Qty</th>
                <th style="width: 12%">Date of Completion</th>
                <th style="width: 10%">Checked by</th>
                <th style="width: 10%">Contractor</th>
            </tr>
        </thead>
        <tbody>
            @php
                $stageIds = explode('|', $product->stage_ids ?? '0');
                $stageIds = array_filter($stageIds, function($id) { return $id != '0'; });
                $srNo = 1;
            @endphp
            
            @foreach($stage as $stageItem)
                @if(in_array($stageItem->head_id, $stageIds))
                    <tr>
                        <td style="text-align: center;">{{ $srNo }}</td>
                        <td>{{ $stageItem->name ?? 'N/A' }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @php $srNo++; @endphp
                @endif
            @endforeach
            
            {{-- PTC-Close Row --}}
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td style="text-align: center;">{{ $srNo }}</td>
                <td>PTC-Close</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

@endsection


@extends('print.layout')

@section('title', 'PTC_Template_' . ($product->article_no ?? 'N/A'))

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

<style>
    .document-title {
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
        text-decoration: underline;
    }

    .document-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        border: 1px solid #ddd;
        padding: 15px;
        background-color: #f9f9f9;
    }

    .info-section {
        flex: 1;
        margin-right: 20px;
    }

    .info-section:last-child {
        margin-right: 0;
    }

    .info-row {
        display: flex;
        margin-bottom: 8px;
    }

    .info-label {
        font-weight: bold;
        width: 120px;
        margin-right: 10px;
    }

    .info-value {
        flex: 1;
    }

    .document-section {
        margin-top: 30px;
    }

    .document-section h3 {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 15px;
        border-bottom: 2px solid #333;
        padding-bottom: 5px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .print-table thead {
        background-color: #e8e8e8;
    }

    .print-table th {
        border: 1px solid #333;
        padding: 8px;
        text-align: left;
        font-weight: bold;
        font-size: 12px;
    }

    .print-table td {
        border: 1px solid #333;
        padding: 8px;
        height: 30px;
        font-size: 12px;
    }

    @media print {
        body {
            margin: 0;
            padding: 10px;
        }
        .print-table {
            page-break-inside: avoid;
        }
    }
</style>
@endsection


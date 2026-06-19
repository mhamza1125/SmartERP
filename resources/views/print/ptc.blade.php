@extends('print.layout')

@section('title', 'PTC_' . ($ptc->stock_no ?? 'N/A') . '_' . ($ptc->stock_date ?? date('d-m-Y')))

@push('styles')
<style>
    .document-info  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .info-section   { border: 1px solid #ddd; padding: 12px !important;}
    .info-value     { flex: 1; text-align: right; }
    .document-section { margin-bottom: 20px; }
    .document-section h3 { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 4px; }
    .summary-info   { border: 1px solid #ddd; padding: 12px; }
    .summary-row    { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 11px; }
    .summary-label  { font-weight: bold; }
    .summary-value  { text-align: right; }
    .alert          { padding: 10px; margin-bottom: 16px; border: 1px solid #ddd; }
    .alert-warning  { background-color: #fff3cd; border-color: #ffc107; color: #856404; }
</style>
@endpush

@section('content')
<div class="document-title">Process Travel Card (PTC)</div>

{{-- PTC Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">PTC No:</span>
            <span class="info-value">PTC-{{ $ptc->stock_no ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value">{{ $ptc->stock_date ? \Carbon\Carbon::parse($ptc->stock_date)->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order:</span>
            <span class="info-value">{{ $ptc->job_no ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @if($ptc->stock_status == 6)
                    In Progress
                @elseif($ptc->stock_status == 7)
                    Completed
                @else
                    Unknown
                @endif
            </span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Product:</span>
            <span class="info-value">{{ $product->name ?? 'N/A' }} - {{ $product->size_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Quantity:</span>
            <span class="info-value">{{ $ptc->description ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Stage:</span>
            <span class="info-value">{{ $ptc->current_stage_name ?? 'Completed' }}</span>
        </div>
    </div>
</div>

{{-- Issuances and Receivings Table --}}
@if($movements && $movements->count() > 0)
<div class="document-section">
    <h3>Production Activities</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 4%">Sr.</th>
                <th style="width: 18%">Reference</th>
                <th style="width: 12%">Type</th>
                <th style="width: 18%">Stage</th>
                <th style="width: 12%">Avg Qty</th>
                <th style="width: 12%">Date</th>
                <th style="width: 24%">By</th>
            </tr>
        </thead>
        <tbody>
            @php
                $issuanceSeqMap = [];
                foreach($issuances as $idx => $iss) {
                    $issuanceSeqMap[$iss->stock_id] = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                }
            @endphp
            @foreach($movements as $index => $movement)
                @php
                    if ($movement->is_ptc_master == 1) {
                        $displayRef = 'PTC-' . $ptc->stock_no . ' / I001';
                    } elseif ($movement->stock_type == 2) {
                        $issSeq = $issuanceSeqMap[$movement->stock_id] ?? $movement->stock_no;
                        $displayRef = 'PTC-' . $ptc->stock_no . ' / I' . $issSeq;
                    } else {
                        $displayRef = 'PTC-' . $ptc->stock_no . ' / ' . $movement->stock_no;
                    }

                    // Calculate average quantity for issuances and receivings
                    $avgQty = '-';

                    if (isset($movementItems[$movement->stock_id])) {
                        $items = $movementItems[$movement->stock_id];

                        if ($items->isNotEmpty()) {
                            if ($movement->stock_type == 1) {
                                // Receiving transaction - show the quantity of products received
                                $receivedQty = $items->where('material_id', 0)->min('quantity');
                                if ($receivedQty !== null) {
                                    $avgQty = $receivedQty;
                                }
                            } elseif ($movement->stock_type == 2 || $movement->is_ptc_master == 1) {
                                // Issuance transaction - calculate theoretical product quantity
                                // Check if materials or products were issued
                                $hasMaterials = $items->where('material_id', '>', 0)->count() > 0;
                                $hasProducts = $items->where('product_type_id', '>', 0)->where('material_id', 0)->count() > 0;

                                if ($hasMaterials && !$hasProducts) {
                                    // Only materials issued - calculate theoretical product quantity
                                    $theoreticalQties = [];
                                    foreach ($items as $item) {
                                        if ($item->material_id > 0 && isset($productMaterials[$item->material_id])) {
                                            $materialReq = $productMaterials[$item->material_id]->quantity;
                                            if ($materialReq > 0) {
                                                $theoreticalQties[] = $item->quantity / $materialReq;
                                            }
                                        }
                                    }
                                    if (!empty($theoreticalQties)) {
                                        $avgQty = number_format(min($theoreticalQties), 2);
                                    }
                                } elseif ($hasProducts) {
                                    // Products issued - show minimum quantity
                                    $minQty = $items->where('material_id', 0)->min('quantity');
                                    if ($minQty !== null) {
                                        $avgQty = $minQty;
                                    }
                                }
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $displayRef }}</td>
                    <td>
                        @if($movement->is_ptc_master == 1)
                            Initial Issue
                        @elseif($movement->stock_type == 1)
                            Receive
                        @else
                            Issue
                        @endif
                    </td>
                    <td>
                        @if($movement->stock_type == 1)
                            {{-- For receives: show the stage from which materials were received --}}
                            {{ $movement->stage_name ?? 'N/A' }}
                        @else
                            {{-- For issues (including initial issuance): show the stage for which materials were issued --}}
                            {{ $movement->issue_stage_name ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-center">{{ $avgQty }}</td>
                    <td class="text-center">{{ $movement->stock_date ? \Carbon\Carbon::parse($movement->stock_date)->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $movement->employee_name ?? $movement->vendor_name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Summary Section --}}
<div class="document-section">
    <h3>Activity Summary</h3>
    <div class="summary-info">
        <div class="summary-row">
            <span class="summary-label">Total Activities:</span>
            <span class="summary-value">{{ $movements->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Issuances:</span>
            <span class="summary-value">{{ $movements->where('stock_type', 2)->count() + ($movements->where('is_ptc_master', 1)->count() > 0 ? 1 : 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Receivings:</span>
            <span class="summary-value">{{ $movements->where('stock_type', 1)->count() }}</span>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <h5>No Activities Recorded</h5>
    <p>No issuances or receivings have been recorded for this PTC yet.</p>
</div>
@endif

@endsection


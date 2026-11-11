@extends('print.layout')

@section('title', 'PTC_' . ($ptc->stock_no ?? 'N/A') . '_' . formatDate($ptc->stock_date ?? now()))
@section('doc_title', 'Process Travel Card')
@section('doc_no', 'PTC-' . ($ptc->stock_no ?? ''))

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
    .ptc-card thead th { text-align: center !important; white-space: normal; }
    .ptc-card tbody td { height: 22px; border: 1px solid var(--hairline); }
    .ptc-card td.text-center { white-space: nowrap; }
    .ptc-card__idle td { color: var(--muted); }
    .ptc-card__note { display: block; font-size: 8.5px; color: var(--muted); }
    .ptc-card__close td { font-weight: 700; background: #f0f0f0; }
    .ptc-card__ref { font-family: var(--mono); font-size: 9px; }
    .ptc-card__ref span { display: block; white-space: nowrap; }
    .ptc-card thead th, .ptc-card tbody td { padding-left: 4px !important; padding-right: 4px !important; }
    .ptc-card__releases { margin-top: 5px; font-size: 10px; color: var(--ink); }
    .qa-box { border: 1px solid var(--hairline); padding: 8px 12px; }
    .qa-box__title { font-weight: 700; font-size: 11.5px; margin-bottom: 6px; }
    .qa-checklist { list-style: none; margin: 0 0 8px; padding: 0; }
    .qa-checklist li { display: flex; align-items: center; gap: 8px; padding: 3px 0; font-size: 11px; }
    .qa-check { display: inline-block; width: 12px; height: 12px; border: 1.2px solid var(--ink); flex-shrink: 0; }
    .qa-declaration { font-size: 10.5px; line-height: 1.5; font-style: italic; margin: 6px 0 0; }
    .qa-signs { margin-top: 22px; }
</style>
@endpush

@section('content')
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
                @elseif($ptc->stock_status == \App\Models\Stock::STATUS_PTC_COMPLETED)
                    Completed
                @elseif($ptc->stock_status == \App\Models\Stock::STATUS_PTC_CLOSED)
                    Closed Early
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
            <span class="info-value">{{ preg_match('/\[QTY:(\d+(?:\.\d+)?)\]/', $ptc->description ?? '', $qtyMatch) ? $qtyMatch[1] : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ $ptc->stock_status == 6 ? 'Current Stage:' : ($ptc->stock_status == \App\Models\Stock::STATUS_PTC_COMPLETED ? 'Completed At Stage:' : 'Closed At Stage:') }}</span>
            <span class="info-value">{{ $ptc->current_stage_name ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- PTC traveller card: every production stage, first to last; stages without activity stay blank --}}
<div class="document-section">
    <div class="table-title">Production Stages</div>
    <table class="print-table ptc-card" data-no-align data-fixed-widths>
        <thead>
            <tr>
                <th style="width: 4%">Sr.</th>
                <th style="width: 17%">Stage</th>
                <th style="width: 9%">Ref</th>
                <th style="width: 10%">Starting Date</th>
                <th style="width: 6%">QTY</th>
                <th style="width: 6%">Re-Work</th>
                <th style="width: 5%">Rej</th>
                <th style="width: 6%">Pass QTY</th>
                <th style="width: 10%">Date of Completion</th>
                <th style="width: 9%">Checked By</th>
                <th style="width: 18%">Contractor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stageCard as $stageRow)
                <tr class="{{ in_array($stageRow->status, ['outside', 'start'], true) ? 'ptc-card__idle' : '' }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        {{ $stageRow->name }}
                        @if($stageRow->status === 'start')<span class="ptc-card__note">starting stock</span>@endif
                    </td>
                    <td class="ptc-card__ref">
                        @foreach(array_filter(explode(', ', (string) $stageRow->refs)) as $ref)<span>{{ $ref }}</span>@endforeach
                    </td>
                    <td class="text-center">{{ $stageRow->started ? formatDate($stageRow->started) : '' }}</td>
                    <td class="num">{{ $stageRow->issued !== null ? $stageRow->issued + 0 : '' }}</td>
                    <td></td>
                    <td class="num">{{ $stageRow->rejected !== null ? $stageRow->rejected + 0 : '' }}</td>
                    <td class="num">{{ $stageRow->passed !== null ? $stageRow->passed + 0 : '' }}</td>
                    <td class="text-center">{{ $stageRow->completed ? formatDate($stageRow->completed) : '' }}</td>
                    <td></td>
                    <td>{{ $stageRow->workers }}</td>
                </tr>
            @endforeach
            <tr class="ptc-card__close">
                <td class="text-center">{{ $stageCard->count() + 1 }}</td>
                <td>PTC-Close</td>
                @for($col = 0; $col < 9; $col++)<td></td>@endfor
            </tr>
        </tbody>
    </table>
    @php $releases = ($movements ?? collect())->where('stock_status', \App\Models\Stock::STATUS_PTC_RELEASED); @endphp
    @if($releases->isNotEmpty())
        <div class="ptc-card__releases">
            <strong>Released to general stock:</strong>
            @foreach($releases as $release)
                {{ $ptcRefs[$release->stock_id] ?? 'REL' }} on {{ formatDate($release->stock_date) }}
                ({{ ($movementItems[$release->stock_id] ?? collect())->sum('quantity') + 0 }})@if(!$loop->last), @endif
            @endforeach
        </div>
    @endif
</div>

{{-- QA DHR completion checklist, ticked and signed by hand --}}
<div class="document-section avoid-break">
    <div class="table-title">Production Activities</div>
    <div class="qa-box">
        <div class="qa-box__title">QA DHR Completion Checklist</div>
        <ol class="qa-checklist">
            <li><span class="qa-check"></span> 1. Process traveler fully completed</li>
            <li><span class="qa-check"></span> 2. QA Inspection Checklist</li>
            <li><span class="qa-check"></span> 3. Re-Work / Rejection entry (if applicable)</li>
            <li><span class="qa-check"></span> 4. Material test, hardness test report (if applicable)</li>
        </ol>
        <p class="qa-declaration">
            Points on this checklist have been reviewed by QA and have been found to be completed and have been corrected
            to assure all the necessary information about the above noted lot has been included.
        </p>
        <div class="sign-row qa-signs">
            <span class="sign-row__field">QA Manager:<span class="sign-row__line"></span></span>
            <span class="sign-row__field">Date:<span class="sign-row__line"></span></span>
        </div>
    </div>
</div>

{{-- Movement Log and Activity Summary: hidden for now (2026-11-12) - the Production Stages table above covers
     the same issues and receipts (with their references). To show them again, remove this comment's first and last lines.
<!-- Issuances and Receivings Table -->
@if($movements && $movements->count() > 0)
<div class="document-section">
    <div class="table-title">Movement Log</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 4%">Sr.</th>
                <th style="width: 18%">Reference</th>
                <th style="width: 12%">Type</th>
                <th style="width: 18%">Stage (From &rarr; To)</th>
                <th style="width: 12%">Avg QTY</th>
                <th style="width: 12%">Date</th>
                <th style="width: 24%">To / From</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $index => $movement)
                @php
                    // I{seq} / R{n}-I{seq} / REL{n}, numbered the same way as the PTC page
                    $displayRef = 'PTC-' . $ptc->stock_no . ' / ' . ($ptcRefs[$movement->stock_id] ?? \App\Models\Stock::ptcDisplayNo($movement->stock_no, $ptc->stock_no));

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
                        @elseif($movement->stock_status == \App\Models\Stock::STATUS_PTC_RELEASED)
                            Released to Stock
                        @elseif($movement->stock_type == 1)
                            Receive
                        @else
                            Issue
                        @endif
                    </td>
                    <td>
                        <!-- From -> To, same as the PTC page's Stage Movement column -->
                        @php $move = $stageMoves[$movement->stock_id] ?? ['from' => [], 'to' => []]; @endphp
                        {{ collect($move['from'])->pluck('label')->unique()->implode(', ') ?: 'N/A' }}
                        &rarr;
                        {{ collect($move['to'])->pluck('label')->unique()->implode(', ') ?: 'N/A' }}
                    </td>
                    <td class="text-center">{{ $avgQty }}</td>
                    <td class="text-center">{{ $movement->stock_date ? \Carbon\Carbon::parse($movement->stock_date)->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $movement->employee_name ?? $movement->vendor_name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Summary Section -->
<div class="document-section">
    <h3>Activity Summary</h3>
    <div class="summary-info">
        <div class="summary-row">
            <span class="summary-label">Total Activities:</span>
            <span class="summary-value">{{ $movements->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Issuances:</span>
            <!-- The initial (master) issuance is itself stock_type 2 -->
            <span class="summary-value">{{ $movements->where('stock_type', 2)->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Receivings:</span>
            <span class="summary-value">{{ $movements->where('stock_type', 1)->where('stock_status', '!=', \App\Models\Stock::STATUS_PTC_RELEASED)->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Releases to Stock:</span>
            <span class="summary-value">{{ $movements->where('stock_status', \App\Models\Stock::STATUS_PTC_RELEASED)->count() }}</span>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <h5>No Activities Recorded</h5>
    <p>No issuances or receivings have been recorded for this PTC yet.</p>
</div>
@endif
--}}

@endsection


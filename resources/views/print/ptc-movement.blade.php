@extends('print.layout')

{{--
    PTC gate pass: one issuance, receipt or release of a PTC (what the View modal on the PTC page
    shows), with signature lines. Issuances go out to the worker/contractor (outward pass),
    receipts come back in (inward pass), releases move PTC stock into general stock.
--}}
@php
    $isRelease = $movement->stock_status == \App\Models\Stock::STATUS_PTC_RELEASED;
    $isReceipt = !$isRelease && $movement->stock_type == 1;
    $kind = $movement->is_ptc_master == 1 ? 'Initial Issue' : ($isRelease ? 'Released to Stock' : ($isReceipt ? 'Receive' : 'Issue'));
    $docTitle = $isRelease ? 'Stock Release Note' : ($isReceipt ? 'Gate Pass (Inward)' : 'Gate Pass (Outward)');
    $fullRef = 'PTC-' . $ptc->stock_no . ' / ' . $reference;

    $partyName = $movement->employee_name ?? $movement->vendor_name;
    $partyNo = $movement->employee_no ?? $movement->vendor_no;
    $partyPhone = $movement->employee_phone ?? $movement->vendor_phone;
    $partyLabel = $isReceipt ? 'Received From' : 'Issued To';

    $stageText = function ($side) use ($stageMove) {
        return collect($stageMove[$side] ?? [])->pluck('label')->unique()->implode(', ') ?: '-';
    };
    $productQty = $items->filter(fn ($item) => $item->material_id == 0 && !$item->component_product_type_id)->sum('quantity');
    $notes = trim(strip_tags($movement->description ?? ''));
@endphp

@section('title', 'Gate_Pass_PTC-' . $ptc->stock_no . '_' . $reference)
@section('doc_title', $docTitle)
@section('doc_no', $fullRef)

@section('content')

<div class="meta-band meta-band--grid meta-band--cols-4">
    <div class="meta-field">
        <span class="meta-label">Pass No</span>
        <span class="meta-value mono">{{ $fullRef }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Date</span>
        <span class="meta-value mono">{{ $movement->stock_date ? \Carbon\Carbon::parse($movement->stock_date)->format('d-m-Y') : 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Type</span>
        <span class="meta-value">{{ $kind }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $ptc->job_no ?? 'Default PTC' }}</span>
    </div>
    @unless($isRelease)
    <div class="meta-field meta-field--span2">
        <span class="meta-label">{{ $partyLabel }}</span>
        <span class="meta-value">{{ $partyName ? trim(($partyNo ? $partyNo . ' - ' : '') . $partyName) : '-' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Phone</span>
        <span class="meta-value mono">{{ $partyPhone ?: '-' }}</span>
    </div>
    @endunless
    @if($issuanceRef)
    <div class="meta-field">
        <span class="meta-label">Against Issuance</span>
        <span class="meta-value mono">PTC-{{ $ptc->stock_no }} / {{ $issuanceRef }}</span>
    </div>
    @endif
    <div class="meta-field meta-field--span2">
        <span class="meta-label">PTC Product</span>
        <span class="meta-value">{{ $product ? trim(($product->article_no ? $product->article_no . ' - ' : '') . $product->name . ' (' . $product->size_name . ')') : 'N/A' }}</span>
    </div>
    <div class="meta-field meta-field--span2">
        <span class="meta-label">Stage Movement</span>
        <span class="meta-value">{{ $stageText('from') }} &rarr; {{ $stageText('to') }}</span>
    </div>
</div>

<div class="items-section">
    <div class="section-head">Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 32px;">Sr.</th>
                <th>Type</th>
                <th>Item</th>
                <th>Stage</th>
                <th class="num">Qty</th>
                @if($isReceipt)
                    <th>Work Done</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @if($item->component_product_type_id)
                        <td>Component</td>
                        <td>{{ $item->component_article_no ?? '' }} - {{ $item->component_name ?? 'N/A' }} ({{ $item->component_size ?? '' }})</td>
                        <td>{{ $item->stage_name ?? '-' }}</td>
                    @elseif($item->material_id > 0)
                        <td>Material</td>
                        <td>{{ $item->material_name ?? 'N/A' }}</td>
                        <td>-</td>
                    @else
                        <td>Product{{ $item->ptc_virtual ? ' (PTC stock)' : '' }}</td>
                        <td>{{ $item->article_no ? $item->article_no . ' - ' : '' }}{{ $item->product_name ?? 'N/A' }} ({{ $item->size_name ?? 'N/A' }})</td>
                        <td>{{ $item->stage_name ?? 'N/A' }}</td>
                    @endif
                    <td class="num">{{ $item->quantity + 0 }}{{ $item->material_id > 0 && $item->unit_name ? ' ' . $item->unit_name : '' }}</td>
                    @if($isReceipt)
                        <td>{{ $item->work_names ?: '-' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isReceipt ? 6 : 5 }}" style="text-align: center;">No items.</td>
                </tr>
            @endforelse
        </tbody>
        @if($productQty > 0)
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;">Total Product Qty</th>
                    <th class="num">{{ $productQty + 0 }}</th>
                    @if($isReceipt)
                        <th></th>
                    @endif
                </tr>
            </tfoot>
        @endif
    </table>
</div>

@if($notes !== '')
<div class="avoid-break" style="margin-top: 10px;">
    <div class="section-head">Notes</div>
    <div class="note-box">{{ $notes }}</div>
</div>
@endif

@unless($isRelease)
<div class="sign-row">
    <span class="sign-row__field">Time {{ $isReceipt ? 'In' : 'Out' }}<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Vehicle / Carried By<span class="sign-row__line"></span></span>
</div>
@endunless

<div class="signatures avoid-break" style="margin-top: 40px;">
    @if($isRelease)
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Released By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Store Keeper</div>
        </div>
    @elseif($isReceipt)
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Delivered By (Worker / Contractor)</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Received By (Store)</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Security / Gate</div>
        </div>
    @else
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Issued By (Store)</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Received By (Worker / Contractor)</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Security / Gate</div>
        </div>
    @endif
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-label">Authorized Signatory</div>
    </div>
</div>

@endsection

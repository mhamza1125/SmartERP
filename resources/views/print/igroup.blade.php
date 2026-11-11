@extends('print.layout')

@section('title', 'Item_Group_' . ($igroup['igroup_no'] ?? 'N/A') . '_' . formatDate($igroup['igroup_date'] ?? now()))
@section('doc_title', 'Item Group')
@section('doc_no', $igroup['igroup_no'] ?? '')

@section('content')
{{-- Item Group Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Item Group No</span>
            <span class="meta-value">{{ $igroup['igroup_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Date</span>
            <span class="meta-value">{{ formatDate($igroup['igroup_date'] ?? null, 'N/A') }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Job No</span>
            <span class="meta-value">{{ $igroup['job_no'] ?? 'Default' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Status</span>
            <span class="meta-value">{{ $igroup['status'] ?? 'Active' }}</span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($igroup['description']) && !empty($igroup['description']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($igroup['description']))) !!}
    </div>
</div>
@endif

{{-- Item Group Items --}}
@if(isset($igroupItem) && $igroupItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Item Group Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Material / Stage</th>
                <th>QTY</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($igroupItem as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->article_no ?? 'N/A' }} - Size {{ $item->sname ?? 'N/A' }}</td>
                <td>{{ ($item->name) ? $item->material_no . " - " . $item->name : ($item->stage ?? 'N/A') }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0) }}</td>
                <td>{{ ($item->uname) ? $item->uname : ($item->puname ?? 'N/A') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total Items:</strong></td>
                <td class="text-right"><strong>{{ $igroupItem->count() }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Items in Group:</span>
        <span>{{ $igroupItem->count() ?? 0 }}</span>
    </div>
    <div class="total-row">
        <span>Group Status:</span>
        <span>{{ $igroup['status'] ?? 'Active' }}</span>
    </div>
</div>

@endsection

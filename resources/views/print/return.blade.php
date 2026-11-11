@extends('print.layout')

@section('title', 'Return_' . ($return['return_no'] ?? 'N/A') . '_' . formatDate($return['return_date'] ?? now()))
@section('doc_title', 'Purchase Return')
@section('doc_no', $return['return_no'] ?? '')

@section('content')

{{-- Return Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Return No</span>
            <span class="meta-value">{{ $return['return_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Receive No</span>
            <span class="meta-value">{{ $return['receive_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">P.O.#</span>
            <span class="meta-value">{{ $return['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Job No</span>
            <span class="meta-value">{{ $return['job_no'] ?? 'Default Purchase' }}</span>
        </div>
    </div>
    
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Vendor</span>
            <span class="meta-value">{{ $return['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $return['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Address</span>
            <span class="meta-value">{{ $return['address'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Return Date</span>
            <span class="meta-value">{{ !empty($return['return_date']) ? \Carbon\Carbon::parse($return['return_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        @if(isset($return['receive_date']) && !empty($return['receive_date']))
            <div class="meta-field">
                <span class="meta-label">Received Date</span>
                <span class="meta-value">{{ !empty($return['receive_date']) ? \Carbon\Carbon::parse($return['receive_date'])->format('d-m-Y') : 'N/A' }}</span>
            </div>
        @endif
    </div>
</div>

{{-- Description --}}
@if(isset($return['desc']) && !empty($return['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($return['desc']))) !!}
    </div>
</div>
@endif

{{-- Returned Items (materials or products) --}}
@if(isset($returnMaterial) && $returnMaterial->count() > 0)
@php $isMaterial = ($return['purchase_type'] ?? '') == 'material'; @endphp
<div class="items-section avoid-break">
    <div class="table-title">Returned Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 6%">Sr.</th>
                <th class="text-center" style="width: 14%">{{ $isMaterial ? 'Material No' : 'Article No' }}</th>
                <th class="text-left">{{ $isMaterial ? 'Material' : 'Product' }}</th>
                <th class="text-center" style="width: 12%">{{ $isMaterial ? 'Unit' : 'Size' }}</th>
                <th class="num" style="width: 12%">QTY</th>
                <th class="text-left" style="width: 22%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnMaterial as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $isMaterial ? ($item->material_no ?? '') : ($item->article_no ?? '') }}</td>
                <td class="text-left">{{ $item->name ?? '' }}</td>
                <td class="text-center">{{ $item->hname ?? '' }}</td>
                <td class="num">{{ number_format($item->quantity ?? 0, 2) }}</td>
                <td class="text-left">{{ $item->remarks ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total</td>
                <td class="num">{{ number_format($returnMaterial->sum('quantity'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

<div class="sign-row">
    <span class="sign-row__field">Issued By:<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Received By:<span class="sign-row__line"></span></span>
</div>

@endsection

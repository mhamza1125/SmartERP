@extends('print.layout')

@section('title', 'Material_Detail_Report_' . formatDate($dfrom ?? now()) . '_to_' . formatDate($dto ?? now()))
@section('doc_title', 'Material Detail Report')

@section('content')
{{-- Report Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Report Date</span>
            <span class="meta-value">{{ date('d-m-Y') }}</span>
        </div>
        @php $selectedMaterial = $material->where('material_id', $mid)->first(); @endphp
        <div class="meta-field">
            <span class="meta-label">Material</span>
            <span class="meta-value">{{ $selectedMaterial ? ($selectedMaterial->material_no . ' - ' . $selectedMaterial->name) : 'All Materials' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Period From</span>
            <span class="meta-value">{{ isset($dfrom) ? date('d-m-Y', strtotime($dfrom)) : 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Period To</span>
            <span class="meta-value">{{ isset($dto) ? date('d-m-Y', strtotime($dto)) : 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Material Ledger Items --}}
@if(isset($materialItem) && $materialItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Material Ledger</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Date</th>
                <th>Material No</th>
                <th>Material</th>
                <th>Purchase / Issuance</th>
                <th>Stock In</th>
                <th>Stock Out</th>
            </tr>
        </thead>
        <tbody>
            @php $loopIndex = 1; @endphp
            @foreach($materialItem as $item)
            <tr>
                <td>{{ $loopIndex++ }}</td>
                <td>{{ (new DateTime($item->timestamp))->format('d-m-Y') }}</td>
                <td>{{ $item->material_no ?? 'N/A' }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                @if(isset($item->purchase_id) && !isset($item->return_material_id))
                    <td>Purchase</td>
                    <td class="text-right">{{ $item->total_received ?? '0' }} {{ $item->uname ?? '' }}</td>
                    <td></td>
                @elseif(isset($item->purchase_id) && isset($item->return_material_id))
                    <td>Return</td>
                    <td class="text-right">{{ $item->total_returned ?? '0' }} {{ $item->uname ?? '' }}</td>
                    <td></td>
                @elseif(isset($item->stock_id))
                    <td>Issuance</td>
                    <td></td>
                    <td class="text-right">{{ $item->quantity ?? '0' }} {{ $item->uname ?? '' }}</td>
                @else
                    <td>N/A</td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="items-section avoid-break">
    <p style="text-align: center; color: #999;">No material ledger records found for the selected period.</p>
</div>
@endif

@endsection


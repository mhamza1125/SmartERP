@extends('print.layout')

@section('title', 'Packing_List_' . ($packingList->delivery_no ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Packing List')
@section('doc_no', $packingList->delivery_no ?? '')

@push('scripts')
<script>
    window.print();
</script>
@endpush

@section('content')
<div class="document-info" style="margin-bottom:12px;">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice No:</span>
            <span class="info-value">{{ $packingList->delivery_no ?? 'N/A' }}</span>
        </div>
    </div>
    <div class="info-section">
    </div>
</div>

@php
    // Optional details only get a column / row when at least one value exists
    $hasWeight = $cartons->contains(fn ($carton) => (float) $carton->box_weight > 0)
        || ((float) $packingList->pallet_weight > 0 && $packingList->pallet_qty);
    $colCount = $hasWeight ? 5 : 4;
    $grandTotalPieces = 0;
    $grandTotalWeight = 0;
@endphp

<table class="print-table">
    <thead>
        <tr>
            <th class="text-left" style="width:22%">Carton Details</th>
            <th class="text-left">Description of Goods</th>
            <th class="num" style="width:12%">Pcs / Carton</th>
            <th class="num" style="width:14%">Total Pcs</th>
            @if($hasWeight)
                <th class="num" style="width:14%">Weight</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @foreach($cartons as $carton)
        @php
            $cartonQty    = $carton->carton_to - $carton->carton_from + 1;
            $cartonTotal  = 0;
            $cartonWeight = (float) $carton->box_weight * $cartonQty;
            $itemCount    = $carton->items->count();
        @endphp

        @foreach($carton->items as $index => $item)
        <tr>
            @if($index === 0)
                <td rowspan="{{ $itemCount }}" style="font-weight:bold; vertical-align:top;">
                    Carton # {{ $carton->carton_from }}@if($carton->carton_from != $carton->carton_to) to {{ $carton->carton_to }}@endif
                </td>
            @endif
            <td>{{ $item->name }}</td>
            <td class="num">{{ number_format($item->pcs_each_carton) }}</td>
            <td class="num">{{ number_format($item->pcs_each_carton * $cartonQty) }}</td>
            @if($hasWeight)
                <td class="num">@if($index === 0 && $cartonWeight > 0){{ number_format($cartonWeight, 2) }}@endif</td>
            @endif
        </tr>
        @php $cartonTotal += $item->pcs_each_carton * $cartonQty; @endphp
        @endforeach

        <tr style="background-color:#f9f9f9;">
            <td style="font-style:italic; color:#666; font-size:10px;">
                @if($carton->box_dimension)Box Dimension: {{ $carton->box_dimension }}@endif
            </td>
            <td colspan="2" class="text-right" style="font-weight:bold;">Group Total</td>
            <td class="num" style="font-weight:bold;">{{ number_format($cartonTotal) }}</td>
            @if($hasWeight)
                <td class="num" style="font-weight:bold;">@if($cartonWeight > 0){{ number_format($cartonWeight, 2) }}@endif</td>
            @endif
        </tr>

        @php
            $grandTotalPieces += $cartonTotal;
            $grandTotalWeight += $cartonWeight;
        @endphp
    @endforeach

    @if($packingList->pallet_qty)
        @php $palletTotalWeight = (float) $packingList->pallet_weight * $packingList->pallet_qty; @endphp
        <tr style="border-top:2px solid #000;">
            <td style="font-weight:bold;">
                {{ $packingList->pallet_qty }} Pallet{{ $packingList->pallet_qty > 1 ? 's' : '' }}
            </td>
            <td colspan="{{ $colCount - ($hasWeight ? 2 : 1) }}" style="font-style:italic; color:#666; font-size:10px;">
                @if($packingList->pallet_dimension)Pallet Dimension: {{ $packingList->pallet_dimension }}@endif
            </td>
            @if($hasWeight)
                <td class="num">@if($palletTotalWeight > 0){{ number_format($palletTotalWeight, 2) }}@endif</td>
            @endif
        </tr>
        @php $grandTotalWeight += $palletTotalWeight; @endphp
    @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right">Total{{ $hasWeight ? ' (Pcs and Weight)' : ' Pcs' }}</td>
            <td class="num">{{ number_format($grandTotalPieces) }}</td>
            @if($hasWeight)
                <td class="num">{{ number_format($grandTotalWeight, 2) }}</td>
            @endif
        </tr>
    </tfoot>
</table>
@endsection

@extends('print.layout')

@section('title', 'Packing_List_' . ($packingList->delivery_no ?? 'N/A') . '_' . date('d-m-Y'))

@push('scripts')
<script>
    window.print();
</script>
@endpush

@section('content')
<div class="document-title">Packing List</div>

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

<table class="print-table">
    <thead>
        <tr>
            <th style="width:20%">Carton Details</th>
            <th style="width:35%">Description of Goods</th>
            <th style="width:10%" class="text-center">Pcs</th>
            <th style="width:15%" class="text-right">Total Pcs</th>
            <th style="width:20%" class="text-right">Weight</th>
        </tr>
    </thead>
    <tbody>
    @php
        $grandTotalPieces = 0;
        $grandTotalWeight = 0;
    @endphp

    @foreach($cartons as $carton)
        @php
            $cartonQty   = $carton->carton_to - $carton->carton_from + 1;
            $cartonTotal  = 0;
            $cartonWeight = 0;
            $itemCount    = $carton->items->count();
        @endphp

        @foreach($carton->items as $index => $item)
        <tr>
            @if($index === 0)
                <td rowspan="{{ $itemCount }}" style="font-weight:bold; vertical-align:top;">
                    @if($carton->carton_from == $carton->carton_to)
                        Carton # {{ $carton->carton_from }}
                    @else
                        Carton # {{ $carton->carton_from }} to {{ $carton->carton_to }}
                    @endif
                </td>
            @endif
            <td>{{ $item->name }}</td>
            <td class="text-center">{{ $item->pcs_each_carton }} Each</td>
            <td class="text-right">{{ $item->pcs_each_carton * $cartonQty }}</td>
            <td class="text-right">
                @if($carton->box_weight)
                    @php $cartonWeight += $carton->box_weight * $cartonQty; @endphp
                    {{ number_format($carton->box_weight * $cartonQty, 2) }}
                @endif
            </td>
        </tr>
        @php $cartonTotal += $item->pcs_each_carton * $cartonQty; @endphp
        @endforeach

        @if($carton->box_dimension)
        <tr>
            <td style="font-style:italic; color:#666; font-size:10px;">Box Dimension: {{ $carton->box_dimension }}</td>
            <td colspan="2"></td>
            <td class="text-right" style="font-weight:bold;">Group Total: {{ $cartonTotal }}</td>
            <td class="text-right" style="font-weight:bold;">{{ number_format($cartonWeight, 2) }}</td>
        </tr>
        @else
        <tr style="background-color:#f9f9f9;">
            <td></td>
            <td colspan="2"></td>
            <td class="text-right" style="font-weight:bold;">Group Total: {{ $cartonTotal }}</td>
            <td class="text-right" style="font-weight:bold;">{{ number_format($cartonWeight, 2) }}</td>
        </tr>
        @endif

        @php
            $grandTotalPieces += $cartonTotal;
            $grandTotalWeight += $cartonWeight;
        @endphp
    @endforeach

    @if($packingList->pallet_qty)
        <tr style="border-top:2px solid #000;">
            <td style="font-weight:bold;">
                {{ $packingList->pallet_qty }} Pallet{{ $packingList->pallet_qty > 1 ? 's' : '' }}
            </td>
            <td colspan="3"></td>
            <td class="text-right">
                @if($packingList->pallet_weight)
                    {{ number_format($packingList->pallet_weight, 2) }}
                @endif
            </td>
        </tr>
        @if($packingList->pallet_dimension)
        <tr>
            <td style="font-style:italic; color:#666; font-size:10px;">
                Pallet Dimension: {{ $packingList->pallet_dimension }}
            </td>
            <td colspan="3"></td>
            <td class="text-right">
                @if($packingList->pallet_weight && $packingList->pallet_qty)
                    @php $palletTotalWeight = $packingList->pallet_weight * $packingList->pallet_qty; @endphp
                    {{ number_format($palletTotalWeight, 2) }}
                @endif
            </td>
        </tr>
        @endif
        @php
            if ($packingList->pallet_weight && $packingList->pallet_qty) {
                $grandTotalWeight += $packingList->pallet_weight * $packingList->pallet_qty;
            }
        @endphp
    @endif

    <tr style="border-top:2px solid #000; font-weight:bold; background-color:#f0f0f0;">
        <td>Total (Pcs and Weight)</td>
        <td></td>
        <td></td>
        <td class="text-right">{{ $grandTotalPieces }}</td>
        <td class="text-right">{{ number_format($grandTotalWeight, 2) }}</td>
    </tr>
    </tbody>
</table>
@endsection

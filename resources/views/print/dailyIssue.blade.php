@extends('print.layout')

@section('title', 'Daily_Issuance_Report_' . ($dfrom ?? date('d-m-Y')) . '_to_' . ($dto ?? date('d-m-Y')))

@section('content')
<div class="document-title">Daily Issuance Report</div>

{{-- Report Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Report Date:</span>
            <span class="info-value">{{ date('d-m-Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period From:</span>
            <span class="info-value">{{ isset($dfrom) ? date('d-m-Y', strtotime($dfrom)) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period To:</span>
            <span class="info-value">{{ isset($dto) ? date('d-m-Y', strtotime($dto)) : 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Daily Issuance Items --}}
@if(isset($issueItem) && $issueItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Issued Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Item / Product</th>
                <th>Size</th>
                <th>Stage</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $index = 1; 
                $product_id = ''; 
                $size = '';
                $issueItemUnique = $issueItem->unique(function ($item) {
                    return $item->product_type_id . '|' . $item->ifname;
                });
            @endphp
            @foreach($issueItemUnique as $item)
                @php 
                    $currentKey = $item->product_type_id . '|' . $item->ifname;
                    $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0';
                @endphp
                @if($minAvg > 0)
                <tr>
                    <td>{{ $index++ }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="2"></td>
                    @else
                        <td>{{ $item->article_no ?? 'N/A' }}</td>
                        <td>{{ $item->pname ?? 'N/A' }}</td>
                    @endif
                    @if($item->sname == $size)
                        <td></td>
                    @else
                        <td>{{ $item->sname ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $item->ifname ?? 'N/A' }}</td>
                    <td class="text-right">{{ $minAvg }} {{ $item->puname ?? '' }}</td>
                </tr>
                @endif
                @php 
                    $product_id = $item->product_id; 
                    $size = $item->sname;
                @endphp
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="items-section avoid-break">
    <p style="text-align: center; color: #999;">No issuance records found for the selected period.</p>
</div>
@endif

@endsection


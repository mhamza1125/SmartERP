@extends('print.layout')

@section('title', 'Daily_Receiving_Report_' . ($dfrom ?? date('Y-m-d')) . '_to_' . ($dto ?? date('Y-m-d')))

@section('content')
<div class="document-title">Daily Receiving Report</div>

{{-- Report Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Report Date:</span>
            <span class="info-value">{{ date('d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period From:</span>
            <span class="info-value">{{ isset($dfrom) ? date('d F Y', strtotime($dfrom)) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period To:</span>
            <span class="info-value">{{ isset($dto) ? date('d F Y', strtotime($dto)) : 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Daily Receiving Items --}}
@if(isset($issueItem) && $issueItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Received Items</h3>
    <table class="items-table">
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
            @endphp
            @foreach($issueItem as $item)
            <tr>
                <td>{{ $index++ }}</td>
                @if($item->product_id == $product_id)
                    <td colspan="2"></td>
                @else
                    <td>{{ $item->article_no ?? 'N/A' }}</td>
                    <td>{{ $item->name ?? 'N/A' }}</td>
                @endif
                @if($item->sname == $size)
                    <td></td>
                @else
                    <td>{{ $item->sname ?? 'N/A' }}</td>
                @endif
                <td>{{ $item->stname ?? 'N/A' }}</td>
                <td class="text-right">{{ $item->stockIn ?? '0' }} {{ $item->puname ?? '' }}</td>
            </tr>
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
    <p style="text-align: center; color: #999;">No receiving records found for the selected period.</p>
</div>
@endif

@endsection


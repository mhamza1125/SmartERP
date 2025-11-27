@extends('print.layout')

@section('title', 'Stock_Report_' . date('Y-m-d'))

@section('content')
<div class="document-title">Stock Report</div>

<div style="text-align: center; margin-bottom: 20px; font-size: 11px;">
    <p><strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
</div>

@if(($type === 'all' || $type === 'material') && isset($stock) && $stock->count())
<div class="avoid-break">
    <h3>Material Stock</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Code</th>
                <th>Material Name</th>
                <th>Type</th>
                <th style="text-align: right;">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $materialIndex = 1; @endphp
            @foreach($stock as $item)
                @unless($item->material_type_id == 101)
                <tr>
                    <td>{{ $materialIndex++ }}</td>
                    <td>{{ $item->material_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->mtname }}</td>
                    <td style="text-align: right;">{{ number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(($type === 'all' || $type === 'product') && isset($pstock) && $pstock->count())
<div class="avoid-break">
    <h3>Product Stock</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Product Name</th>
                <th>Size</th>
                <th>Stage</th>
                <th style="text-align: right;">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $productIndex = 1; @endphp
            @foreach($pstock as $item)
                @if($item->stockIn - $item->stockOut != 0)
                <tr>
                    <td>{{ $productIndex++ }}</td>
                    <td>{{ $item->article_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sname }}</td>
                    <td>{{ $item->stname }}</td>
                    <td style="text-align: right;">{{ number_format($item->stockIn - $item->stockOut) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(($type === 'all' || $type === 'machine') && isset($stock) && $stock->count())
<div class="avoid-break">
    <h3>Machine Material Stock</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Code</th>
                <th>Material Name</th>
                <th style="text-align: right;">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $machineIndex = 1; @endphp
            @foreach($stock as $item)
                @unless($item->material_type_id != 101)
                <tr>
                    <td>{{ $machineIndex++ }}</td>
                    <td>{{ $item->material_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td style="text-align: right;">{{ number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

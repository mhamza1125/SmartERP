@extends('print.layout')

@section('title', 'Production_Order_' . ($order['job_no'] ?? 'N/A') . '_' . formatDate($order['order_date'] ?? now()))
@section('doc_title', 'Production Order')
@section('doc_no', $order['job_no'] ?? '')

@section('content')

{{-- Production Order Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $order['job_no'] ?? 'N/A' }}</span>
        </div>
        @if(isset($order['description']) && !empty($order['description']))
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ substr($order['description'], 0, 100) }}{{ strlen($order['description']) > 100 ? '...' : '' }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{ !empty($order['order_date']) ? \Carbon\Carbon::parse($order['order_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        @if(isset($order['due_date']) && !empty($order['due_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order['due_date'])->format('d-m-Y') }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Production Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 6%">Sr.</th>
                <th class="text-center" style="width: 12%">Article No</th>
                <th class="text-left" style="width: 26%">Product Name</th>
                <th class="text-center" style="width: 14%">Size</th>
                <th class="num" style="width: 10%">QTY</th>
                <th class="num" style="width: 11%">Total Stock</th>
                <th class="num" style="width: 10%">Finished</th>
                <th class="num" style="width: 11%">Unfinished</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItem as $item)
            @php
                $stock = $stockData[$item->product_type_id . '_' . $item->product_stage_id] ?? [];
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->article_no }}</td>
                <td class="text-left">{{ $item->pname }}</td>
                <td class="text-center">{{ $item->name }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format($stock['total_stock'] ?? 0) }}</td>
                <td class="num">{{ number_format($stock['finished_stock'] ?? 0) }}</td>
                <td class="num">{{ number_format($stock['unfinished_stock'] ?? 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Additional Notes Section --}}
@if(isset($order['description']) && !empty($order['description']))
<div class="info-section avoid-break">
    <div class="table-title">Production Notes</div>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($order['description']))) !!}
    </div>
</div>
@endif

@endsection


@extends('print.layout')

@section('title', 'Production_Order_' . ($order['job_no'] ?? 'N/A') . '_' . ($order['order_date'] ?? date('d-m-Y')))

@section('content')
<div class="document-title">Production Order</div>

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
            <span class="info-value">{{ $order['order_date'] ?? 'N/A' }}</span>
        </div>
        @if(isset($order['due_date']) && !empty($order['due_date']))
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ $order['due_date'] }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Production Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <h3>Production Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 6%">Sr.</th>
                <th style="width: 10%">Article No</th>
                <th style="width: 20%">Product Name</th>
                <th style="width: 10%">Size</th>
                <th style="width: 20%">Stage</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 12%">Finished Stock</th>
                <th style="width: 12%">Unfinished Stock</th>
            </tr>
        </thead>
        <tbody>
            @if($orderItem->count())
                @php $product_id = 0; @endphp
                @foreach($orderItem as $item)
                @php
                    $key = $item->product_type_id . '_' . $item->product_stage_id;
                    $finishedStock = $stockData[$key]['finished_stock'] ?? 0;
                    $unfinishedStock = $stockData[$key]['unfinished_stock'] ?? 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="3"></td>
                    @else
                        <td class="text-center">{{ $item->article_no }}</td>
                        <td class="text-center">{{ $item->pname }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        @php $product_id = $item->product_id; @endphp
                    @endif
                    <td class="text-center">{{ $item->sname ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-center">{{ number_format($finishedStock) }}</td>
                    <td class="text-center">{{ number_format($unfinishedStock) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Additional Notes Section --}}
@if(isset($order['description']) && !empty($order['description']))
<div class="info-section avoid-break">
    <h3>Production Notes</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($order['description']))) !!}
    </div>
</div>
@endif

@endsection


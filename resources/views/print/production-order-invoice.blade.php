@extends('print.layout')

@section('title', 'Production_Order_' . ($order->job_no ?? 'N/A') . '_' . ($order->order_date ?? date('d-m-Y')))

@push('scripts')
<script>
    window.onload = function () {
        window.print();
        window.onafterprint = function () { window.close(); };
    };
</script>
@endpush

@section('content')
<div class="document-title">Production Order</div>

<div class="document-info">
    <div class="info-section">
        <h3 style="margin:0 0 8px; font-size:12px; border-bottom:1px solid #ccc; padding-bottom:4px;">Production Information</h3>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $order->job_no }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</span>
        </div>
        @if($order->due_date)
        <div class="info-row">
            <span class="info-label">Delivery Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order->due_date)->format('d-m-Y') }}</span>
        </div>
        @endif
    </div>

    @if($order->description)
    <div class="info-section avoid-break">
        <h3 style="margin:0 0 8px; font-size:12px; border-bottom:1px solid #ccc; padding-bottom:4px;">Production Details</h3>
        <div class="note-box">{!! $order->description !!}</div>
    </div>
    @endif
</div>

@if(isset($orderItems) && $orderItems->count())
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                <th style="width:12%">Article No</th>
                <th>Product Name</th>
                <th style="width:10%">Size</th>
                <th style="width:12%">Stage</th>
                <th style="width:10%" class="text-center">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItems as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->article_no }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->hname }}</td>
                <td>{{ $item->sname }}</td>
                <td class="text-center">{{ number_format($item->quantity) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

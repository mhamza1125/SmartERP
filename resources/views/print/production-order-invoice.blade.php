@extends('print.layout')

@section('title', 'Production_Order_' . ($order->job_no ?? 'N/A') . '_' . formatDate($order->order_date ?? now()))
@section('doc_title', 'Production Order')
@section('doc_no', $order->job_no ?? '')

@push('scripts')
<script>
    window.onload = function () {
        window.print();
        window.onafterprint = function () { window.close(); };
    };
</script>
@endpush

@section('content')
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $order->job_no }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Order Date</span>
        <span class="meta-value mono">{{ formatDate($order->order_date, 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Delivery Date</span>
        <span class="meta-value mono">{{ formatDate($order->due_date) }}</span>
    </div>
</div>

@if($order->description)
<div class="avoid-break" style="margin-bottom:12px;">
    <div class="section-head">Production Details</div>
    <div class="note-box">{!! $order->description !!}</div>
</div>
@endif

@if(isset($orderItems) && $orderItems->count())
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                <th style="width:12%">Article No</th>
                <th>Product</th>
                <th style="width:10%">Size</th>
                <th style="width:12%">Stage</th>
                <th style="width:10%" class="text-center">QTY</th>
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

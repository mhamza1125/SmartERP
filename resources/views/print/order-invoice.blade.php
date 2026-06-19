@extends('print.layout')

@section('title', 'Order_Invoice_' . ($order->order_no ?? 'N/A') . '_' . ($order->order_date ?? date('d-m-Y')))

@push('scripts')
<script>
    window.onload = function () {
        window.print();
        window.onafterprint = function () { window.close(); };
    };
</script>
@endpush

@section('content')

<div class="doc-title-row">
    <span class="doc-title-label">ORDER INVOICE</span>
    <span class="doc-title-no"># {{ $order->order_no ?? 'N/A' }}</span>
</div>

{{-- ── Meta band ──────────────────────────────────────────────────────────── --}}
<div class="meta-band">
    <div class="meta-field">
        <span class="meta-label">Order No</span>
        <span class="meta-value mono">{{ $order->order_no }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $order->job_no }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Order Date</span>
        <span class="meta-value mono">{{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</span>
    </div>
    @if($order->due_date)
    <div class="meta-field">
        <span class="meta-label">Due Date</span>
        <span class="meta-value mono">{{ \Carbon\Carbon::parse($order->due_date)->format('d-m-Y') }}</span>
    </div>
    @endif
    @if($order->payment_terms)
    <div class="meta-field">
        <span class="meta-label">Payment Terms</span>
        <span class="meta-value">{{ $order->payment_terms }}</span>
    </div>
    @endif
</div>

{{-- ── Customer ─────────────────────────────────────────────────────────────── --}}
<div class="party-block">
    <div class="party-label">CUSTOMER</div>
    <div class="party-name">{{ $customer->fname }} {{ $customer->lname }}</div>
    @if($customer->address)
        <div class="party-detail">{{ $customer->address }}</div>
    @endif
    <div class="party-contact">
        @if($customer->phone){{ $customer->phone }}@endif
        @if($customer->phone && $customer->email)  |  @endif
        @if($customer->email){{ $customer->email }}@endif
    </div>
    @if($customer->fi_no || $customer->rex_no || $customer->ntn)
        <div class="party-detail" style="margin-top:3px;">
            @if($customer->fi_no)FI: {{ $customer->fi_no }}  @endif
            @if($customer->rex_no)REX: {{ $customer->rex_no }}  @endif
            @if($customer->ntn)NTN: {{ $customer->ntn }}@endif
        </div>
    @endif
</div>

{{-- ── Order Items ──────────────────────────────────────────────────────────── --}}
@if(isset($orderItems) && $orderItems->count())
<div class="avoid-break">
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                <th style="width:12%" class="text-center">Article No</th>
                <th>Product Name</th>
                <th style="width:10%" class="text-center">Size</th>
                <th style="width:10%" class="text-center">Stage</th>
                <th style="width:9%" class="text-right">Qty</th>
                <th style="width:11%" class="text-right">Unit Price</th>
                <th style="width:11%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItems as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="code">{{ $item->article_no }}</td>
                <td>{{ $item->name }}</td>
                <td class="text-center">{{ $item->hname }}</td>
                <td class="text-center">{{ $item->sname }}</td>
                <td class="num">{{ number_format($item->quantity) }}</td>
                <td class="num">{{ number_format($item->price, 2) }}</td>
                <td class="num">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="totals-wrap avoid-break">
    <div class="totals-block">
        <div class="totals-block__row grand">
            <span class="lbl">TOTAL AMOUNT</span>
            <span class="val">{{ number_format($orderItems->sum('total'), 2) }}</span>
        </div>
    </div>
</div>
@endif

@endsection

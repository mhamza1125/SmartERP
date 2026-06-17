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
<div class="document-title">Order Invoice</div>

<div class="document-info">
    <div class="info-section">
        <h3 style="margin:0 0 8px; font-size:12px; border-bottom:1px solid #ccc; padding-bottom:4px;">Order Information</h3>
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $order->order_no }}</span>
        </div>
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
            <span class="info-label">Due Date:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($order->due_date)->format('d-m-Y') }}</span>
        </div>
        @endif
        @if($order->payment_terms)
        <div class="info-row">
            <span class="info-label">Payment Terms:</span>
            <span class="info-value">{{ $order->payment_terms }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <h3 style="margin:0 0 8px; font-size:12px; border-bottom:1px solid #ccc; padding-bottom:4px;">Customer Information</h3>
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value">{{ $customer->fname }} {{ $customer->lname }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $customer->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $customer->phone }}</span>
        </div>
        @if($customer->address)
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $customer->address }}</span>
        </div>
        @endif
        @if($customer->fi_no)
        <div class="info-row">
            <span class="info-label">FI No:</span>
            <span class="info-value">{{ $customer->fi_no }}</span>
        </div>
        @endif
        @if($customer->rex_no)
        <div class="info-row">
            <span class="info-label">REX No:</span>
            <span class="info-value">{{ $customer->rex_no }}</span>
        </div>
        @endif
        @if($customer->ntn)
        <div class="info-row">
            <span class="info-label">NTN:</span>
            <span class="info-value">{{ $customer->ntn }}</span>
        </div>
        @endif
    </div>
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
                <th style="width:10%">Stage</th>
                <th style="width:10%" class="text-center">Qty</th>
                <th style="width:11%" class="text-right">Unit Price</th>
                <th style="width:11%" class="text-right">Total</th>
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
                <td class="text-right amount">{{ number_format($item->price, 2) }}</td>
                <td class="text-right amount">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total Amount:</td>
                <td class="text-right amount">{{ number_format($orderItems->sum('total'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif
@endsection

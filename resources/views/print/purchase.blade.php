@extends('print.layout')

@section('title', (isset($process) ? 'Material_Process_' : 'Purchase_Order_') . ($purchase['purchase_no'] ?? 'N/A') . '_' . ($purchase['purchase_date'] ?? date('d-m-Y')))

@section('content')

<div class="doc-title-row">
    <span class="doc-title-label">{{ isset($process) ? 'MATERIAL PROCESSING' : 'PURCHASE ORDER' }}</span>
    <span class="doc-title-no"># {{ $purchase['purchase_no'] ?? 'N/A' }}</span>
</div>

{{-- ── Meta band ──────────────────────────────────────────────────────────── --}}
<div class="meta-band">
    <div class="meta-field">
        <span class="meta-label">Purchase No</span>
        <span class="meta-value mono">{{ $purchase['purchase_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Date</span>
        <span class="meta-value mono">{{ $purchase['purchase_date'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Required By</span>
        <span class="meta-value mono">{{ $purchase['require_date'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Job No</span>
        <span class="meta-value mono">{{ $purchase['job_no'] ?? 'Default Purchase' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Type</span>
        <span class="meta-value">{{ ucfirst($purchase['purchase_type'] ?? 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Status</span>
        <span class="meta-value">
            @if(isset($purchase['status']))
                @if($purchase['status'] == 'completed')
                    <span class="status-pill pill-success">Completed</span>
                @elseif($purchase['status'] == 'pending')
                    <span class="status-pill pill-warning">Pending</span>
                @else
                    <span class="status-pill pill-info">{{ ucfirst($purchase['status']) }}</span>
                @endif
            @else
                <span class="status-pill pill-secondary">N/A</span>
            @endif
        </span>
    </div>
</div>

{{-- ── Vendor ─────────────────────────────────────────────────────────────── --}}
@if(!empty($purchase['fname']))
<div class="party-block">
    <div class="party-label">{{ $purchase['vendor_type'] ?? 'vendor' == 'contractor' ? 'CONTRACTOR' : 'VENDOR' }}</div>
    <div class="party-name">
        @if(!empty($purchase['vendor_no'])){{ $purchase['vendor_no'] }} — @endif{{ $purchase['fname'] }}
    </div>
    @if(!empty($purchase['address']))
        <div class="party-detail">{{ $purchase['address'] }}</div>
    @endif
    @if(!empty($purchase['phone1']))
        <div class="party-contact">{{ $purchase['phone1'] }}</div>
    @endif
</div>
@endif

{{-- ── Description ─────────────────────────────────────────────────────────── --}}
@if(!empty($purchase['desc']))
<div class="avoid-break" style="margin-bottom:12px;">
    <div class="section-head">Description</div>
    <div class="note-box">{!! nl2br(e(strip_tags($purchase['desc']))) !!}</div>
</div>
@endif

{{-- ── Purchase Items ──────────────────────────────────────────────────────── --}}
@if(isset($purchaseItem) && $purchaseItem->count() > 0)
<div class="items-section avoid-break">
    <div class="section-head">Purchase Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                @if($purchase['purchase_type'] == 'material')
                    <th style="width:12%" class="text-center">Material No</th>
                    <th>Material Name</th>
                    <th style="width:10%" class="text-center">Unit</th>
                @else
                    <th style="width:12%" class="text-center">Article No</th>
                    <th>Product Name</th>
                    <th style="width:10%" class="text-center">Size</th>
                @endif
                <th style="width:10%" class="text-right">Qty</th>
                <th style="width:11%" class="text-right">Rate</th>
                <th style="width:12%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach($purchaseItem as $item)
            @php
                $amount = ($item->quantity ?? 0) * ($item->price ?? 0);
                $totalAmount += $amount;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                @if($purchase['purchase_type'] == 'material')
                    <td class="code">{{ $item->material_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @else
                    <td class="code">{{ $item->article_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @endif
                <td class="text-center">{{ $item->hname ?? '' }}</td>
                <td class="num">{{ number_format($item->quantity ?? 0, 2) }}</td>
                <td class="num">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="num">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Totals --}}
<div class="totals-wrap avoid-break">
    <div class="totals-block">
        <div class="totals-block__row grand">
            <span class="lbl">GRAND TOTAL</span>
            <span class="val">{{ number_format($totalAmount ?? 0, 2) }}</span>
        </div>
    </div>
</div>

@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <span class="amount-words-label">Amount in Words:</span>
    {{ numberToWordsWithCurrency($totalAmount ?? 0) }}
</div>
@endif
@endif

{{-- ── Receive Summary ─────────────────────────────────────────────────────── --}}
@if(isset($receiveSum) && $receiveSum->count() > 0)
<div class="items-section avoid-break" style="margin-top:14px;">
    <div class="section-head">Receive Summary</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                @if($purchase['purchase_type'] == 'material')
                    <th style="width:12%" class="text-center">Material No</th>
                    <th>Material Name</th>
                    <th style="width:10%" class="text-center">Unit</th>
                @else
                    <th style="width:12%" class="text-center">Article No</th>
                    <th>Product Name</th>
                    <th style="width:10%" class="text-center">Size</th>
                @endif
                <th style="width:10%" class="text-right">Order Qty</th>
                <th style="width:10%" class="text-right">Recv Qty</th>
                <th style="width:10%" class="text-right">Return Qty</th>
                <th style="width:10%" class="text-right">Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receiveSum as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                @if($purchase['purchase_type'] == 'material')
                    <td class="code">{{ $item->material_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @else
                    <td class="code">{{ $item->article_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @endif
                <td class="text-center">{{ $item->hname }}</td>
                <td class="num">{{ $item->quantity }}</td>
                <td class="num">{{ $item->rqty }}</td>
                <td class="num">{{ $item->rqty2 ?? '0' }}</td>
                <td class="num">{{ $item->quantity - $item->rqty + $item->rqty2 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Receive Records ─────────────────────────────────────────────────────── --}}
@if(isset($receiveTimes) && count($receiveTimes) > 0)
<div class="items-section avoid-break" style="margin-top:14px;">
    <div class="section-head">Receive Records</div>
    @foreach($receiveTimes as $receiveTime)
    <div class="receive-record">
        <span class="mono">{{ $receiveTime->receive_no ?? 'N/A' }}</span>
        &mdash;
        {{ $receiveTime->receive_date ? \Carbon\Carbon::parse($receiveTime->receive_date)->format('d-m-Y') : 'N/A' }}
    </div>
    @endforeach
</div>
@endif

{{-- ── Payment Records ─────────────────────────────────────────────────────── --}}
@if(isset($paymentTimes) && count($paymentTimes) > 0)
<div class="items-section avoid-break" style="margin-top:14px;">
    <div class="section-head">Payment Records</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%">Sr.</th>
                <th style="width:16%">Date</th>
                <th style="width:18%" class="text-right">Amount</th>
                <th>Payment Method</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPaid = 0; @endphp
            @foreach($paymentTimes as $payment)
            @php $totalPaid += ($payment['amount'] ?? 0); @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="mono">{{ $payment['payment_date'] ?? 'N/A' }}</td>
                <td class="num">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                <td>{{ $payment['payment_method'] ?? 'N/A' }}</td>
                <td class="mono">{{ $payment['reference'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">Total Paid:</td>
                <td class="num">{{ number_format($totalPaid, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection

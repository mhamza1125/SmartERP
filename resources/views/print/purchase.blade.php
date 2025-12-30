@extends('print.layout')

@section('title', (isset($process) ? 'Material_Process_' : 'Purchase_Order_') . ($purchase['purchase_no'] ?? 'N/A') . '_' . ($purchase['purchase_date'] ?? date('Y-m-d')))

@section('content')
<div class="document-title">{{ isset($process) ? 'Material Processing' : 'Purchase Order' }}</div>

{{-- Purchase Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Purchase No:</span>
            <span class="info-value">{{ $purchase['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Purchase Date:</span>
            <span class="info-value">{{ $purchase['purchase_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Required Date:</span>
            <span class="info-value">{{ $purchase['require_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $purchase['job_no'] ?? 'Default Purchase' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor:</span>
            <span class="info-value">{{ $purchase['vendor_no'] ?? '' }} - {{ $purchase['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $purchase['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $purchase['address'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @if(isset($purchase['status']))
                    @if($purchase['status'] == 'completed')
                        <span class="badge badge-success">Completed</span>
                    @elseif($purchase['status'] == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @else
                        <span class="badge badge-info">{{ ucfirst($purchase['status']) }}</span>
                    @endif
                @else
                    <span class="badge badge-secondary">N/A</span>
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($purchase['desc']) && !empty($purchase['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($purchase['desc']))) !!}
    </div>
</div>
@endif

{{-- Purchase Items --}}
@if(isset($purchaseItem) && $purchaseItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Purchase Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material Code</th>
                <th>Material Name</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Rate</th>
                <th>Amount</th>
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
                <td>{{ $item->material_no ?? 'N/A' }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0, 2) }}</td>
                <td>{{ $item->uname ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Purchase Summary --}}
<div class="totals-section1 avoid-break">
    {{-- <div class="total-row">
        <span>Total Items:</span>
        <span>{{ $purchaseItem->count() ?? 0 }}</span>
    </div> --}}
    <div class="total-row grand-total">
        <span>Grand Total:
            @if(function_exists('numberToWordsWithCurrency'))
                {{ numberToWordsWithCurrency($totalAmount ?? 0) }}
            @endif
        </span>
        <span class="amount">{{ number_format($totalAmount ?? 0, 2) }}</span>
    </div>
</div>

{{-- Receive Summary --}}
@if(isset($receiveSum) && $receiveSum->count() > 0)
<div class="items-section avoid-break">
    <h3>Receive Summary</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material / Product</th>
                <th>Units</th>
                <th>Order Qty</th>
                <th>Receive Qty</th>
                <th>Return Qty</th>
                <th>Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receiveSum as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    @if($purchase['purchase_type'] == 'material')
                        {{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}
                    @else
                        {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                    @endif
                </td>
                <td>{{ $item->hname }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ $item->rqty }}</td>
                <td class="text-right">{{ $item->rqty2 ?? '0' }}</td>
                <td class="text-right">{{ $item->quantity - $item->rqty + $item->rqty2 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Receive Records --}}
@if(isset($receiveTimes) && count($receiveTimes) > 0)
<div class="items-section avoid-break">
    <h3>Receive Records</h3>
    @foreach($receiveTimes as $index => $receiveTime)
    <div class="receive-record" style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;">
        <h4>{{ $receiveTime->receive_no ?? 'N/A' }} - {{ $receiveTime->receive_date ?? 'N/A' }}</h4>
    </div>
    @endforeach
</div>
@endif

{{-- Payment Records --}}
@if(isset($paymentTimes) && count($paymentTimes) > 0)
<div class="items-section avoid-break">
    <h3>Payment Records</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPaid = 0; @endphp
            @foreach($paymentTimes as $payment)
            @php $totalPaid += ($payment['amount'] ?? 0); @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $payment['payment_date'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                <td>{{ $payment['payment_method'] ?? 'N/A' }}</td>
                <td>{{ $payment['reference'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>Total Paid:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPaid, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection

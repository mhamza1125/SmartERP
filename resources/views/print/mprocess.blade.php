@extends('print.layout')

@section('title', 'Material_Process_' . ($purchase['purchase_no'] ?? 'N/A') . '_' . ($purchase['purchase_date'] ?? date('Y-m-d')))

@section('content')
<div class="document-title">Material Processing</div>

{{-- Material Process Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Processing No:</span>
            <span class="info-value">{{ $purchase['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Processing Date:</span>
            <span class="info-value">{{ $purchase['purchase_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Required Date:</span>
            <span class="info-value">{{ $purchase['require_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $purchase['job_no'] ?? 'Default Processing' }}</span>
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
        {!! nl2br(e($purchase['desc'])) !!}
    </div>
</div>
@endif

{{-- Material Process Items --}}
@if(isset($purchaseItem) && $purchaseItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Processing Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material A</th>
                <th>Material B</th>
                <th>Quantity A</th>
                <th>Quantity B</th>
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->pmaterial_no ?? 'N/A' }} - {{ $item->pname ?? 'N/A' }}</td>
                <td>{{ $item->material_no ?? 'N/A' }} - {{ $item->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->before_qty ?? 0) }} {{ $item->phname ?? '' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0) }} {{ $item->hname ?? '' }}</td>
                <td class="text-right">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Processing Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Items:</span>
        <span>{{ $purchaseItem->count() ?? 0 }}</span>
    </div>
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($totalAmount ?? 0, 2) }}</span>
    </div>
</div>

{{-- Receive Summary --}}
@if(isset($receiveSum) && $receiveSum->count() > 0)
<div class="items-section avoid-break">
    <h3>Receive Summary</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material</th>
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}</td>
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

@endsection


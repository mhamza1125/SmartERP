@extends('print.layout')

@section('title', 'Receive Information')

@section('content')

{{-- Receive Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Receive No:</span>
            <span class="info-value">{{ $receive['receive_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">P.O.#:</span>
            <span class="info-value">{{ $receive['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $receive['job_no'] ?? 'Default Purchase' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Required Date:</span>
            <span class="info-value">{{ $receive['require_date'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor:</span>
            <span class="info-value">{{ $receive['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $receive['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $receive['address'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Receive Date:</span>
            <span class="info-value">{{ !empty($receive['receive_date']) ? \Carbon\Carbon::parse($receive['receive_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                @php
                    $statusText = 'Pending';
                    if(isset($receive['stock_status'])) {
                        if($receive['stock_status'] == 1) {
                            $statusText = 'Completely Received';
                        } elseif($receive['stock_status'] == 2) {
                            $statusText = 'Partially Received';
                        }
                    }
                @endphp
                {{ $statusText }}
            </span>
        </div>
    </div>
</div>

{{-- Description --}}
@if(isset($receive['desc']) && !empty($receive['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($receive['desc']))) !!}
    </div>
</div>
@endif

{{-- Received Materials --}}
@if(isset($receiveMaterial) && $receiveMaterial->count() > 0)
<div class="items-section avoid-break">
    <h3>Received Materials</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                @if($receive['purchase_type'] == 'material')
                    <th>Material No</th>
                    <th>Material Name</th>
                    <th>Unit</th>
                @else
                    <th>Article No</th>
                    <th>Product Name</th>
                    <th>Size</th>
                @endif
                <th>Receive Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receiveMaterial as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                @if($receive['purchase_type'] == 'material')
                    <td class="text-center">{{ $item->material_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @else
                    <td class="text-center">{{ $item->article_no ?? '' }}</td>
                    <td>{{ $item->name ?? '' }}</td>
                @endif
                <td class="text-center">{{ $item->hname ?? '' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        {{-- <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total Items:</strong></td>
                <td class="text-right"><strong>{{ $receiveMaterial->count() }}</strong></td>
            </tr>
        </tfoot> --}}
    </table>
</div>
@endif

@endsection

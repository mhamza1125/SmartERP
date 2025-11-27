@extends('print.layout')

@section('title', 'Return Information')

@section('content')

{{-- Return Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Return No:</span>
            <span class="info-value">{{ $return['return_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Receive No:</span>
            <span class="info-value">{{ $return['receive_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">P.O.#:</span>
            <span class="info-value">{{ $return['purchase_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $return['job_no'] ?? 'Default Purchase' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Vendor:</span>
            <span class="info-value">{{ $return['fname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $return['phone1'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $return['address'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Return Date:</span>
            <span class="info-value">{{ $return['return_date'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Additional Information --}}
@if(isset($return['receive_date']) && !empty($return['receive_date']))
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Received Date:</span>
            <span class="info-value">{{ $return['receive_date'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>
@endif

{{-- Description --}}
@if(isset($return['desc']) && !empty($return['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e($return['desc'])) !!}
    </div>
</div>
@endif

{{-- Returned Materials --}}
@if(isset($returnMaterial) && $returnMaterial->count() > 0)
<div class="items-section avoid-break">
    <h3>Returned Materials</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material Code</th>
                <th>Material / Product</th>
                <th>Units</th>
                <th>Return Qty</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnMaterial as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->material_no ?? 'N/A' }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>{{ $item->uname ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0, 2) }}</td>
                <td>{{ $item->remarks ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>Total Items:</strong></td>
                <td class="text-right"><strong>{{ $returnMaterial->count() }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection

@extends('print.layout')

@section('title', 'Receive Issuance Information')

@section('content')

{{-- Receive Issuance Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Receive No:</span>
            <span class="info-value">{{ $issue['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Receive Date:</span>
            <span class="info-value">{{ $issue['stock_date'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Job No:</span>
            <span class="info-value">{{ $issue['job_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order No:</span>
            <span class="info-value">{{ $issue['order_no'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Received From:</span>
            <span class="info-value">
                @if($issue['table_name'] == 'employee')
                    {{ $issue['employee_no'] ?? '' }} - {{ $issue['name'] ?? 'Employee' }}
                @elseif($issue['table_name'] == 'vendor')
                    {{ $issue['vendor_no'] ?? '' }} - {{ $issue['fname'] ?? 'Vendor' }}
                @else
                    {{ $issue['table_name'] ?? 'N/A' }}
                @endif
            </span>
        </div>
        @if($issue['table_name'] == 'employee' && !empty($issue['employee_phone']))
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $issue['employee_phone'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'employee' && !empty($issue['employee_address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $issue['employee_address'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'vendor' && !empty($issue['vendor_phone']))
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $issue['vendor_phone'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'vendor' && !empty($issue['vendor_address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $issue['vendor_address'] }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Description --}}
@if(isset($issue['desc']) && !empty($issue['desc']))
<div class="info-section avoid-break">
    <h3>Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($issue['desc']))) !!}
    </div>
</div>
@endif

{{-- Received Items --}}
@if(isset($issueItem) && $issueItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Received Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Type</th>
                <th>Article No</th>
                <th>Material / Stage / Component</th>
                <th>Work Done</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issueItem as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                @if($item->component_product_type_id)
                    <td><span class="status-badge status-badge-warning">Component</span></td>
                    <td>{{ $item->article_no ?? 'N/A' }} - Size {{ $item->sname ?? '' }}</td>
                    <td>{{ $item->component_article_no ?? 'N/A' }} - {{ $item->component_name ?? 'N/A' }}</td>
                @elseif($item->material_id > 0)
                    <td><span class="status-badge status-badge-info">Material</span></td>
                    <td>{{ $item->article_no ?? 'N/A' }} - Size {{ $item->sname ?? '' }}</td>
                    <td>{{ $item->name ?? 'N/A' }}</td>
                @else
                    <td><span class="status-badge status-badge-success">Product</span></td>
                    <td>{{ $item->article_no ?? 'N/A' }} - Size {{ $item->sname ?? '' }}</td>
                    <td>{{ $item->stage ?? 'N/A' }}</td>
                @endif
                <td>
                    @if(isset($item->work_logs) && $item->work_logs)
                        @foreach(explode('|', $item->work_logs) as $index => $work)
                            @php
                                $headName = isset($head) ? $head->firstWhere('head_id', $work)?->name : null;
                            @endphp
                            @if($headName)
                                {{ $headName }}@if(!$loop->last), @endif
                            @endif
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->quantity ?? 0) }}</td>
                <td>{{ $item->uname ?? $item->puname ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Items:</strong></td>
                <td class="text-right"><strong>{{ $issueItem->count() }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Receive Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Received Items:</span>
        <span>{{ $issueItem->count() ?? 0 }}</span>
    </div>
    <div class="total-row">
        <span>Receive Status:</span>
        <span class="status-badge status-badge-success">Completed</span>
    </div>
</div>

@endsection

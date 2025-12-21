@extends('print.layout')

@section('title', 'Issuance Information')

@section('content')

{{-- Issuance Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Stock No:</span>
            <span class="info-value">{{ $issue['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Issue Date:</span>
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
            <span class="info-label">Issued To:</span>
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

{{-- Issued Items Summary --}}
@if(isset($issueItem) && $issueItem->count() > 0)
<div class="items-section avoid-break">
    <h3>Issued Items Summary</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Material / Stage</th>
                <th>Quantity</th>
                <th>Average</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issueItem as $item)
            @if($item->quantity)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->article_no ?? 'N/A' }} - Size {{ $item->sname ?? '' }}</td>
                <td>{{ ($item->name) ? $item->name : $item->stage }}</td>
                <td class="text-right">{{ $item->quantity }} {{ ($item->uname) ? $item->uname : $item->puname }}</td>
                <td class="text-right">{{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : $item->quantity }} {{ $item->puname }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Detailed Issued Items with Work Done --}}
@if(isset($issueAll) && $issueAll->count() > 0 && isset($totalTimes) && count($totalTimes) > 0)
<div class="items-section avoid-break">
    <h3>Detailed Issued Items</h3>
    @foreach($totalTimes as $i => $receiveTime)
    <div class="receive-record" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px;">
        <h4>Record {{ $i + 1 }} - {{ $receiveTime['stock_no'] ?? 'N/A' }}</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    <th>Material / Stage</th>
                    <th>Work Done</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @php $loopIndex = 1; @endphp
                @foreach($issueAll as $item)
                @if($receiveTime['stock_no'] == $item->stock_no)
                <tr>
                    <td>{{ $loopIndex++ }}</td>
                    <td>{{ $item->article_no }} - Size {{ $item->sname }}</td>
                    <td>{{ ($item->name) ? $item->name : $item->stage }}</td>
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
                    <td class="text-right">{{ $item->quantity }} {{ ($item->uname) ? $item->uname : $item->puname }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endif

{{-- Receive Records --}}
@if(isset($totalTimes) && count($totalTimes) > 0)
<div class="items-section">
    <h3>Receive Records</h3>
    @foreach($totalTimes as $index => $receiveTime)
    <div class="receive-record avoid-break" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px;">
        @php
            $statusText = 'Pending';
            if($receiveTime->stock_status == 1) {
                $statusText = 'Completely Received';
            } elseif($receiveTime->stock_status == 2) {
                $statusText = 'Partially Received';
            }
        @endphp
        <h4>{{ $receiveTime->stock_no }} - {{ $statusText }} ({{ $receiveTime->receive_date ?? 'N/A' }})</h4>
    </div>
    @endforeach
</div>
@endif

@endsection

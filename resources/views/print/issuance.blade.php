@extends('print.layout')

@section('title', 'Issuance_' . ($issue['stock_no'] ?? 'N/A'))
@section('doc_title', 'Stock Issuance')
@section('doc_no', $issue['stock_no'] ?? '')

@section('content')

{{-- Issuance Header Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Stock No</span>
            <span class="meta-value">{{ $issue['stock_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Issue Date</span>
            <span class="meta-value">{{ !empty($issue['stock_date']) ? \Carbon\Carbon::parse($issue['stock_date'])->format('d-m-Y') : 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Job No</span>
            <span class="meta-value">{{ $issue['job_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Order No</span>
            <span class="meta-value">{{ $issue['order_no'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Issued To</span>
            <span class="meta-value">
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
        <div class="meta-field">
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $issue['employee_phone'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'employee' && !empty($issue['employee_address']))
        <div class="meta-field">
            <span class="meta-label">Address</span>
            <span class="meta-value">{{ $issue['employee_address'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'vendor' && !empty($issue['vendor_phone']))
        <div class="meta-field">
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $issue['vendor_phone'] }}</span>
        </div>
        @endif
        @if($issue['table_name'] == 'vendor' && !empty($issue['vendor_address']))
        <div class="meta-field">
            <span class="meta-label">Address</span>
            <span class="meta-value">{{ $issue['vendor_address'] }}</span>
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
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Material / Stage</th>
                <th>QTY</th>
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
        <table class="print-table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    <th>Material / Stage</th>
                    <th>Work Done</th>
                    <th>QTY</th>
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
        <h4>{{ $receiveTime->stock_no }} - {{ $statusText }} ({{ $receiveTime->receive_date ? \Carbon\Carbon::parse($receiveTime->receive_date)->format('d-m-Y') : 'N/A' }})</h4>
    </div>
    @endforeach
</div>
@endif

@endsection

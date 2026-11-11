@extends('print.layout')

@section('title', (isset($process) ? 'Material_Process_' : 'Purchase_Order_') . ($purchase['purchase_no'] ?? 'N/A') . '_' . formatDate($purchase['purchase_date'] ?? now()))
@section('doc_title', isset($process) ? 'Material Processing' : 'Purchase Order')
@section('doc_no', $purchase['purchase_no'] ?? '')

@section('content')

{{-- ── Meta band ──────────────────────────────────────────────────────────── --}}
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">Purchase No</span>
        <span class="meta-value mono">{{ $purchase['purchase_no'] ?? 'N/A' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Date</span>
        <span class="meta-value mono">{{ formatDate($purchase['purchase_date'] ?? null, 'N/A') }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Required By</span>
        <span class="meta-value mono">{{ formatDate($purchase['require_date'] ?? null, 'N/A') }}</span>
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
        @php
            // Receiving status from the items: balance = ordered - received + returned (same as the items table)
            $statusReceipts = collect($receiveSum ?? [])->keyBy('purchase_item_id');
            $statusItems = collect($purchaseItem ?? []);
            $receivedAny = $statusReceipts->sum(fn ($row) => (float) $row->rqty) > 0;
            $balanceLeft = $statusItems->sum(function ($item) use ($statusReceipts) {
                $row = $statusReceipts->get($item->purchase_item_id);

                return max(0, (float) $item->quantity - (float) ($row->rqty ?? 0) + (float) ($row->rqty2 ?? 0));
            });
        @endphp
        <span class="meta-value">
            @if($statusItems->isNotEmpty() && $balanceLeft <= 0)
                <span class="status-pill pill-success">Received</span>
            @elseif($receivedAny)
                <span class="status-pill pill-warning">Partially Received</span>
            @else
                <span class="status-pill pill-danger">To Receive</span>
            @endif
        </span>
    </div>
</div>

{{-- ── Vendor (left) and Receive Records (right) ─────────────────────────── --}}
@php $hasReceipts = isset($receiveTimes) && count($receiveTimes) > 0; @endphp
@if(!empty($purchase['fname']) || $hasReceipts)
<div class="party-grid">
    <div class="party-block">
        @if(!empty($purchase['fname']))
            <div class="party-label">{{ ($purchase['vendor_type'] ?? 'vendor') == 'contractor' ? 'CONTRACTOR' : 'VENDOR' }}</div>
            <div class="party-name">
                @if(!empty($purchase['vendor_no'])){{ $purchase['vendor_no'] }} — @endif{{ $purchase['fname'] }}
            </div>
            @if(!empty($purchase['address']))
                <div class="party-detail">{{ $purchase['address'] }}</div>
            @endif
            @if(!empty($purchase['phone1']))
                <div class="party-contact">{{ $purchase['phone1'] }}</div>
            @endif
        @endif
    </div>
    <div class="party-block">
        <div class="party-label">RECEIVE RECORDS</div>
        @forelse($receiveTimes ?? [] as $receiveTime)
            <div class="party-contact">
                {{ $receiveTime->receive_no ?? 'N/A' }} — {{ formatDate($receiveTime->receive_date ?? null, 'N/A') }}
            </div>
        @empty
            <div class="party-detail">Nothing received yet</div>
        @endforelse
    </div>
</div>
@endif

{{-- ── Description ─────────────────────────────────────────────────────────── --}}
@if(!empty($purchase['desc']))
<div class="avoid-break" style="margin-bottom:12px;">
    <div class="section-head">Description</div>
    <div class="note-box">{!! nl2br(e(strip_tags($purchase['desc']))) !!}</div>
</div>
@endif

{{-- ── Purchase Items with their receiving (ordered / received / returned / balance) ── --}}
@if(isset($purchaseItem) && $purchaseItem->count() > 0)
@php
    $isMaterial = $purchase['purchase_type'] == 'material';
    $received = collect($receiveSum ?? [])->keyBy('purchase_item_id');
    $totalAmount = 0;
@endphp
<div class="items-section avoid-break">
    <div class="table-title">Purchase Items</div>
    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center" style="width:4%">Sr.</th>
                <th class="code" style="width:12%; white-space:nowrap">{{ $isMaterial ? 'Material No' : 'Article No' }}</th>
                <th class="text-left">{{ $isMaterial ? 'Material' : 'Product' }}</th>
                <th class="text-center" style="width:10%">{{ $isMaterial ? 'Unit' : 'Size' }}</th>
                <th class="num" style="width:8%">Ordered</th>
                <th class="num" style="width:8%">Price</th>
                <th class="num" style="width:10%">Total</th>
                <th class="num" style="width:8%">Received</th>
                <th class="num" style="width:8%">Returned</th>
                <th class="num" style="width:8%">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseItem as $item)
            @php
                $amount = ($item->quantity ?? 0) * ($item->price ?? 0);
                $totalAmount += $amount;
                $receipt = $received->get($item->purchase_item_id);
                $receivedQty = (float) ($receipt->rqty ?? 0);
                $returnedQty = (float) ($receipt->rqty2 ?? 0);
                $balanceQty = ($item->quantity ?? 0) - $receivedQty + $returnedQty;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="code" style="white-space:nowrap">{{ $isMaterial ? ($item->material_no ?? '') : ($item->article_no ?? '') }}</td>
                <td>{{ $item->name ?? '' }}</td>
                <td class="text-center">{{ $item->hname ?? '' }}</td>
                <td class="num">{{ number_format($item->quantity ?? 0, 2) }}</td>
                <td class="num">{{ number_format($item->price ?? 0, 2) }}</td>
                <td class="num">{{ number_format($amount, 2) }}</td>
                <td class="num">{{ number_format($receivedQty, 2) }}</td>
                <td class="num">{{ number_format($returnedQty, 2) }}</td>
                <td class="num">{{ number_format($balanceQty, 2) }}</td>
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

{{-- ── Payment Records ─────────────────────────────────────────────────────── --}}
@if(isset($paymentTimes) && count($paymentTimes) > 0)
<div class="items-section avoid-break" style="margin-top:14px;">
    <div class="table-title">Payment Records</div>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width:5%" class="text-center">Sr.</th>
                <th style="width:16%">Date</th>
                <th style="width:18%" class="num">Amount</th>
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
                <td class="mono">{{ formatDate($payment['payment_date'] ?? null, 'N/A') }}</td>
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

@unless(isset($process))
{{-- ── Purchase order notes (English left, Urdu right) ─────────────────────── --}}
<div class="po-notes avoid-break">
    <div class="po-notes__en">
        <div class="po-notes__title">Note:</div>
        <ol>
            <li>Submit the Purchase Order form with the bill.</li>
            <li>Acceptance of late deliveries is at the company's discretion.</li>
            <li>Submit the sample or drawing at the time of delivery.</li>
            <li>Acceptance of excess quantity is at the company's discretion.</li>
            <li>Payment will be made after PO completion, unless agreed otherwise.</li>
        </ol>
    </div>
    <div class="po-notes__ur" dir="rtl" lang="ur">
        <div class="po-notes__title">نوٹ:</div>
        <ol>
            <li>پرچیز آرڈر فارم بل کے ساتھ جمع کروائیں۔</li>
            <li>تاخیر سے آنے والا مال قبول کرنا کمپنی کی صوابدید پر ہوگا۔</li>
            <li>متعلقہ آئٹم کا نمونہ یا ڈرائنگ ڈیلیوری کے وقت جمع کروائیں۔</li>
            <li>اضافی مقدار قبول کرنا کمپنی کی صوابدید پر ہوگا۔</li>
            <li>ادائیگی پرچیز آرڈر مکمل ہونے پر کی جائے گی، الا یہ کہ کچھ اور طے ہو۔</li>
        </ol>
    </div>
</div>

{{-- ── Signatures ──────────────────────────────────────────────────────────── --}}
<div class="sign-row po-signs">
    <span class="sign-row__field">Prepared By:<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Approved By:<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Vendor Sign:<span class="sign-row__line"></span></span>
</div>

{{-- ── Gate Inward Pass: blank rows filled in by hand at the gate ───────────── --}}
<div class="avoid-break" style="margin-top: 16px;">
    <div class="table-title">Gate Inward Pass</div>
    <table class="print-table gate-pass" data-no-align data-fixed-widths>
        <thead>
            <tr>
                <th style="width:11%">Date</th>
                <th>Description</th>
                <th style="width:11%">Qty Received</th>
                <th style="width:11%">Batch No</th>
                <th style="width:11%">Qty Balance</th>
                <th style="width:13%">Qty Rejected / Return</th>
                <th style="width:13%">Receiver Sign</th>
            </tr>
        </thead>
        <tbody>
            @for($row = 0; $row < max(3, isset($purchaseItem) ? $purchaseItem->count() : 0); $row++)
                <tr>
                    @for($col = 0; $col < 7; $col++)<td>&nbsp;</td>@endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endunless

@endsection

@push('styles')
<style>
    .po-notes { display: grid; grid-template-columns: 58fr 42fr; gap: 16px; margin-top: 16px; padding: 8px 10px; border: 1px solid var(--hairline); font-size: 10px; line-height: 1.5; }
    .po-notes__title { font-weight: 700; margin-bottom: 3px; }
    .po-notes ol { margin: 0; padding-left: 16px; }
    .po-notes__ur { font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", "Urdu Typesetting", "Noto Naskh Arabic", "Segoe UI", Arial, sans-serif; font-size: 9px; line-height: 1.7; }
    .po-notes__ur ol { padding-left: 0; padding-right: 18px; }
    .po-signs .sign-row__line { width: 38mm; }
    .gate-pass thead th { white-space: normal; text-align: center !important; }
    .gate-pass tbody td { height: 22px; border: 1px solid var(--hairline); }
    .gate-pass tbody tr:nth-child(even) { background: transparent; }
</style>
@endpush

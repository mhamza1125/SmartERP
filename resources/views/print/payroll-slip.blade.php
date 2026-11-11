@extends('print.layout-voucher')

@php
    $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $item->month)->format('F Y');
    $num = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    $lateTime = intdiv((int) $item->late_minutes, 60) . 'h ' . str_pad((int) $item->late_minutes % 60, 2, '0', STR_PAD_LEFT) . 'm';
    $earnings = $item->salary_for_days + $item->overtime_amount;
    $deductions = $item->late_deduction + $item->temp_advance + $item->fixed_advance_deduction;
@endphp

@section('title', 'Salary_Slip_' . ($item->employee_no ?? $item->employee_id) . '_' . $item->month)
@section('doc_title', 'Salary Slip')
@section('doc_no', $monthLabel)

@push('styles')
<style>
    .slip-employee { display: grid; grid-template-columns: 1fr 1fr; column-gap: 14px; row-gap: 1px; margin-bottom: 6px; }
    .slip-employee .info-row { margin-bottom: 0; }
    .slip-grid { display: grid; grid-template-columns: 0.8fr 1fr 1fr; gap: 8px; margin-bottom: 6px; }
    .slip-box { border: 1px solid var(--hairline); }
    .slip-box__title { background: var(--accent); color: #fff; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; padding: 3px 6px; }
    .slip-line { display: flex; justify-content: space-between; gap: 8px; padding: 2px 6px; font-size: 9px; border-bottom: 1px solid var(--hairline); }
    .slip-line:last-child { border-bottom: none; }
    .slip-line .val { font-family: var(--mono); font-weight: 700; white-space: nowrap; }
    .slip-line.total { background: var(--row-alt); font-weight: 700; }
    .slip-line small { color: var(--muted); }
    .slip-loan { font-size: 9px; padding: 3px 6px; border: 1px solid var(--hairline); margin-bottom: 6px; }
</style>
@endpush

@section('content')
<div class="slip-employee">
    <div class="info-row"><span class="info-label">Employee:</span><span class="info-value"><strong>{{ $item->name }}</strong> ({{ $item->employee_no }})</span></div>
    <div class="info-row"><span class="info-label">Salary Month:</span><span class="info-value">{{ $monthLabel }}</span></div>
    <div class="info-row"><span class="info-label">Designation:</span><span class="info-value">{{ $item->designation ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Payment Date:</span><span class="info-value">{{ formatDate($item->payment_date) }}</span></div>
    <div class="info-row"><span class="info-label">Department:</span><span class="info-value">{{ $item->department ?? '-' }}</span></div>
    <div class="info-row"><span class="info-label">Paid From:</span><span class="info-value">{{ $accountName }}</span></div>
</div>

<div class="slip-grid">
    <div class="slip-box">
        <div class="slip-box__title">Attendance</div>
        <div class="slip-line"><span>Total Working Days</span><span class="val">{{ $num($item->total_working_days) }}</span></div>
        <div class="slip-line"><span>Actual Working Days</span><span class="val">{{ $num($item->actual_working_days) }}</span></div>
        <div class="slip-line"><span>Absent Days</span><span class="val">{{ $num(max(0, $item->total_working_days - $item->actual_working_days)) }}</span></div>
        <div class="slip-line"><span>Overtime Hours</span><span class="val">{{ $num($item->overtime_hours) }}</span></div>
        <div class="slip-line"><span>Late Time</span><span class="val">{{ $lateTime }}</span></div>
    </div>
    <div class="slip-box">
        <div class="slip-box__title">Earnings</div>
        <div class="slip-line"><span>Gross Salary</span><span class="val">{{ number_format($item->gross_salary, 2) }}</span></div>
        <div class="slip-line"><span>Salary for Working Days</span><span class="val">{{ number_format($item->salary_for_days, 2) }}</span></div>
        <div class="slip-line"><span>Overtime <small>{{ $num($item->overtime_hours) }} h x {{ number_format($item->overtime_rate, 2) }}</small></span><span class="val">{{ number_format($item->overtime_amount, 2) }}</span></div>
        <div class="slip-line total"><span>Total Earnings</span><span class="val">{{ number_format($earnings, 2) }}</span></div>
    </div>
    <div class="slip-box">
        <div class="slip-box__title">Deductions</div>
        <div class="slip-line"><span>Late Deduction <small>{{ $lateTime }}</small></span><span class="val">{{ number_format($item->late_deduction, 2) }}</span></div>
        <div class="slip-line"><span>Temporary Advance</span><span class="val">{{ number_format($item->temp_advance, 2) }}</span></div>
        <div class="slip-line"><span>Fixed Advance Deduction</span><span class="val">{{ number_format($item->fixed_advance_deduction, 2) }}</span></div>
        <div class="slip-line total"><span>Total Deductions</span><span class="val">{{ number_format($deductions, 2) }}</span></div>
    </div>
</div>

<div class="slip-loan">
    <strong>Fixed Advance (Loan):</strong> {{ number_format($item->fixed_advance, 2) }}
    &nbsp;&middot;&nbsp; <strong>Deducted this month:</strong> {{ number_format($item->fixed_advance_deduction, 2) }}
    &nbsp;&middot;&nbsp; <strong>Remaining:</strong> {{ number_format($item->remaining_fixed_advance, 2) }}
</div>

<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Total Salary (Final Payable)</span>
        <span class="amount">{{ number_format($item->net_salary, 2) }}</span>
    </div>
</div>

@if(function_exists('numberToWordsWithCurrency'))
<div class="amount-words avoid-break">
    <span class="amount-words-label">Amount in Words:</span>
    <span>{{ numberToWordsWithCurrency($item->net_salary) }}</span>
</div>
@endif

<div class="signatures avoid-break">
    @foreach(['Prepared By', 'Approved By', 'Employee Signature'] as $signature)
        <div class="signature-box">
            <div class="signature-line-sm"></div>
            <div class="signature-label">{{ $signature }}</div>
        </div>
    @endforeach
</div>
@endsection

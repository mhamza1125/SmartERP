@extends('print.layout')

@php
    $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $payroll->month)->format('F Y');
    $num = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    $late = fn ($minutes) => intdiv((int) $minutes, 60) . ':' . str_pad((int) $minutes % 60, 2, '0', STR_PAD_LEFT);
@endphp

@section('title', 'Payroll_' . $payroll->month)
@section('doc_title', 'Salary Sheet')
@section('doc_no', $monthLabel)

@push('styles')
<style>
    @page { size: A4 landscape; margin: 0; }
    @media screen {
        .doc-body, .doc-header__inner, .doc-header__rule { max-width: 297mm !important; }
    }
    .payroll-print { font-size: 8.5px; }
    .payroll-print thead th { font-size: 9px !important; letter-spacing: .04em !important; padding: 4px 4px !important; white-space: normal !important; text-align: center !important; vertical-align: middle; }
    .payroll-print tbody td, .payroll-print tfoot td { padding: 3px 4px !important; }
    .payroll-print .name { font-weight: 600; }
    .payroll-print tfoot td { font-weight: 700; }
</style>
@endpush

@section('content')
<div class="meta-band">
    <div class="meta-field"><span class="meta-label">Month</span><span class="meta-value">{{ $monthLabel }}</span></div>
    <div class="meta-field"><span class="meta-label">Total Working Days</span><span class="meta-value mono">{{ $num($payroll->total_working_days) }}</span></div>
    <div class="meta-field"><span class="meta-label">Hours / Day</span><span class="meta-value mono">{{ $num($payroll->hours_per_day) }}</span></div>
    <div class="meta-field"><span class="meta-label">Overtime Rate</span><span class="meta-value">{{ $num($payroll->overtime_multiplier) }}x hourly wage</span></div>
    <div class="meta-field"><span class="meta-label">Payment Date</span><span class="meta-value mono">{{ formatDate($payroll->payment_date) }}</span></div>
    <div class="meta-field"><span class="meta-label">Paid From</span><span class="meta-value">{{ $accountName }}</span></div>
</div>

<table class="print-table payroll-print" data-no-align data-fixed-widths>
    <thead>
        <tr>
            <th style="width:2.5%">Sr.</th>
            <th style="width:10%">Employee Name</th>
            <th style="width:8%">Designation</th>
            <th>Gross Salary</th>
            <th>Total Working Days</th>
            <th>Actual Working Days</th>
            <th>Absent Days</th>
            <th>Salary for Working Days</th>
            <th>Overtime Hours</th>
            <th>Overtime Rate / Hr</th>
            <th>Overtime Amount</th>
            <th>Late Hours</th>
            <th>Late Deduction</th>
            <th>Temporary Advance</th>
            <th>Fixed Advance</th>
            <th>Fixed Advance Deduction</th>
            <th>Remaining Fixed Advance</th>
            <th>Total Salary</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="name">{{ $item->name }}</td>
                <td>{{ $item->designation ?? '-' }}</td>
                <td class="num">{{ number_format($item->gross_salary, 2) }}</td>
                <td class="num">{{ $num($item->total_working_days) }}</td>
                <td class="num">{{ $num($item->actual_working_days) }}</td>
                <td class="num">{{ $num(max(0, $item->total_working_days - $item->actual_working_days)) }}</td>
                <td class="num">{{ number_format($item->salary_for_days, 2) }}</td>
                <td class="num">{{ $num($item->overtime_hours) }}</td>
                <td class="num">{{ number_format($item->overtime_rate, 2) }}</td>
                <td class="num">{{ number_format($item->overtime_amount, 2) }}</td>
                <td class="num">{{ $late($item->late_minutes) }}</td>
                <td class="num">{{ number_format($item->late_deduction, 2) }}</td>
                <td class="num">{{ number_format($item->temp_advance, 2) }}</td>
                <td class="num">{{ number_format($item->fixed_advance, 2) }}</td>
                <td class="num">{{ number_format($item->fixed_advance_deduction, 2) }}</td>
                <td class="num">{{ number_format($item->remaining_fixed_advance, 2) }}</td>
                <td class="num"><strong>{{ number_format($item->net_salary, 2) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right">Total</td>
            <td class="num">{{ number_format($items->sum('gross_salary'), 2) }}</td>
            <td colspan="3"></td>
            <td class="num">{{ number_format($items->sum('salary_for_days'), 2) }}</td>
            <td colspan="2"></td>
            <td class="num">{{ number_format($items->sum('overtime_amount'), 2) }}</td>
            <td></td>
            <td class="num">{{ number_format($items->sum('late_deduction'), 2) }}</td>
            <td class="num">{{ number_format($items->sum('temp_advance'), 2) }}</td>
            <td class="num">{{ number_format($items->sum('fixed_advance'), 2) }}</td>
            <td class="num">{{ number_format($items->sum('fixed_advance_deduction'), 2) }}</td>
            <td class="num">{{ number_format($items->sum('remaining_fixed_advance'), 2) }}</td>
            <td class="num">{{ number_format($items->sum('net_salary'), 2) }}</td>
        </tr>
    </tfoot>
</table>

<p style="font-size: 8.5px; color: var(--muted); margin-top: 4px;">
    Salary for working days = gross x actual / total working days. Hourly wage = gross / total working days / {{ $num($payroll->hours_per_day) }} hours.
    Overtime = hours x hourly wage x {{ $num($payroll->overtime_multiplier) }}. Late deduction = late hours x hourly wage x {{ $num($payroll->late_multiplier) }}.
    Total salary = salary for working days + overtime - late deduction - temporary advance - fixed advance deduction.
</p>

<div class="sign-row">
    <span class="sign-row__field">Prepared By:<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Checked By:<span class="sign-row__line"></span></span>
    <span class="sign-row__field">Approved By:<span class="sign-row__line"></span></span>
</div>
@endsection

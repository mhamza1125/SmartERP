@extends('index')
@section('content')
@php
  $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $payroll->month)->format('F Y');
  $num = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
  $late = fn ($minutes) => intdiv((int) $minutes, 60) . 'h ' . str_pad((int) $minutes % 60, 2, '0', STR_PAD_LEFT) . 'm';
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Payroll - {{ $monthLabel }}</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.print', $payroll->month) }}" class="btn btn-info" target="_blank"><i class="fas fa-print"></i> Print Payroll</a>
              <a href="{{ route('payroll.edit', $payroll->month) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
              <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Back</a>
            </div>
          </div>
          <div class="card-body payroll-sheet">
            <div class="ptc-page">
              <div class="ptc-facts">
                <div><span class="ptc-fact-label">Total Working Days</span><span class="ptc-fact-value">{{ $num($payroll->total_working_days) }}</span></div>
                <div><span class="ptc-fact-label">Hours / Day</span><span class="ptc-fact-value">{{ $num($payroll->hours_per_day) }}</span></div>
                <div><span class="ptc-fact-label">Overtime Rate</span><span class="ptc-fact-value">{{ $num($payroll->overtime_multiplier) }}x hourly wage</span></div>
                <div><span class="ptc-fact-label">Late Deduction</span><span class="ptc-fact-value">{{ $num($payroll->late_multiplier) }}x hourly wage</span></div>
                <div><span class="ptc-fact-label">Payment Date</span><span class="ptc-fact-value">{{ formatDate($payroll->payment_date) }}</span></div>
                <div><span class="ptc-fact-label">Paid From</span><span class="ptc-fact-value">{{ $accountName }}</span></div>
                <div><span class="ptc-fact-label">Employees</span><span class="ptc-fact-value">{{ $items->count() }}</span></div>
                <div><span class="ptc-fact-label">Total Payable</span><span class="ptc-fact-value">{{ number_format($items->sum('net_salary'), 2) }}</span></div>
              </div>
            </div>
            @if($payroll->description)
              <p class="small text-muted">{{ $payroll->description }}</p>
            @endif

            <div class="table-responsive">
              <table class="table table-sm table-hover payroll-table">
                <thead>
                  <tr>
                    <th rowspan="2">Employee</th>
                    <th rowspan="2" class="num">Gross Salary</th>
                    <th colspan="3" class="group">Attendance (days)</th>
                    <th rowspan="2" class="num">Salary for Days</th>
                    <th colspan="3" class="group">Overtime</th>
                    <th colspan="2" class="group">Late</th>
                    <th rowspan="2" class="num">Temp. Advance</th>
                    <th colspan="3" class="group">Fixed Advance (Loan)</th>
                    <th rowspan="2" class="num">Total Salary</th>
                    <th rowspan="2">Print</th>
                  </tr>
                  <tr>
                    <th class="num">Total</th>
                    <th class="num">Worked</th>
                    <th class="num">Absent</th>
                    <th class="num">Hours</th>
                    <th class="num">Rate/Hr</th>
                    <th class="num">Amount</th>
                    <th class="num">Time</th>
                    <th class="num">Deduction</th>
                    <th class="num">Loan</th>
                    <th class="num">Deduction</th>
                    <th class="num">Remaining</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($items as $item)
                    <tr>
                      <td class="text-nowrap">
                        <strong>{{ $item->name }}</strong>
                        <div class="small text-muted">{{ $item->employee_no }}{{ $item->designation ? ' · ' . $item->designation : '' }}</div>
                      </td>
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
                      <td class="num font-weight-bold">{{ number_format($item->net_salary, 2) }}</td>
                      <td>
                        <a href="{{ route('payroll.slip', $item->payroll_item_id) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Print salary slip">
                          <i class="fas fa-print"></i> Slip
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="17" class="text-center text-muted">No employees in this payroll.</td></tr>
                  @endforelse
                </tbody>
                @if($items->isNotEmpty())
                  <tfoot>
                    <tr>
                      <th class="text-right" colspan="5">Total</th>
                      <th class="num">{{ number_format($items->sum('salary_for_days'), 2) }}</th>
                      <th colspan="2"></th>
                      <th class="num">{{ number_format($items->sum('overtime_amount'), 2) }}</th>
                      <th></th>
                      <th class="num">{{ number_format($items->sum('late_deduction'), 2) }}</th>
                      <th class="num">{{ number_format($items->sum('temp_advance'), 2) }}</th>
                      <th></th>
                      <th class="num">{{ number_format($items->sum('fixed_advance_deduction'), 2) }}</th>
                      <th></th>
                      <th class="num">{{ number_format($items->sum('net_salary'), 2) }}</th>
                      <th></th>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

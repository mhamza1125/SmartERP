@extends('index')
@section('content')
@php
  $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
  $num = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ $editing ? 'Edit Payroll - ' . $monthLabel : 'Create Payroll' }}</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Back to Payroll</a>
            </div>
          </div>
          <div class="card-body payroll-sheet">
            @if(session('warning'))
              <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if ($errors->any())
              <div class="alert alert-danger">
                <strong>Please check:</strong>
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form action="{{ $editing ? route('payroll.update', $selectedMonth) : route('payroll.store') }}" method="POST" id="payrollForm">
              @csrf
              @if($editing) @method('PUT') @endif
              <input type="hidden" name="month" value="{{ $selectedMonth }}">

              {{-- Payroll period: the values every employee's salary is calculated with --}}
              <div class="payroll-header">
                <div class="form-group">
                  <label>Month</label>
                  @if($editing)
                    <input type="text" class="form-control" value="{{ $monthLabel }}" readonly>
                  @else
                    <select class="form-control" id="monthSelector">
                      @foreach($monthOptions as $monthValue => $optionLabel)
                        <option value="{{ $monthValue }}" {{ $monthValue == $selectedMonth ? 'selected' : '' }}>{{ $optionLabel }}</option>
                      @endforeach
                    </select>
                  @endif
                </div>
                <div class="form-group">
                  <label>Total Working Days <span class="text-danger">*</span></label>
                  <input type="number" step="0.5" min="1" max="31" class="form-control js-period" name="total_working_days" id="total_working_days"
                         value="{{ old('total_working_days', $num($period['total_working_days'])) }}" required>
                </div>
                <div class="form-group">
                  <label>Hours / Day <span class="text-danger">*</span></label>
                  <input type="number" step="0.5" min="1" max="24" class="form-control js-period" name="hours_per_day" id="hours_per_day"
                         value="{{ old('hours_per_day', $num($period['hours_per_day'])) }}" required>
                </div>
                <div class="form-group">
                  <label>Overtime Rate <small class="text-muted">x hourly wage</small></label>
                  <input type="number" step="0.05" min="0" max="10" class="form-control js-period" name="overtime_multiplier" id="overtime_multiplier"
                         value="{{ old('overtime_multiplier', $num($period['overtime_multiplier'])) }}" required>
                </div>
                <div class="form-group">
                  <label>Late Deduction <small class="text-muted">x hourly wage</small></label>
                  <input type="number" step="0.05" min="0" max="10" class="form-control js-period" name="late_multiplier" id="late_multiplier"
                         value="{{ old('late_multiplier', $num($period['late_multiplier'])) }}" required>
                </div>
                <div class="form-group">
                  <label>Payment Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="payment_date"
                         value="{{ old('payment_date', $payroll?->payment_date ?? date('Y-m-d')) }}" required>
                </div>
                <div class="form-group payroll-header__wide">
                  <label>Pay From (Bank / Cash) <span class="text-danger">*</span></label>
                  @php $selectedBank = (string) old('bank_id', $payroll?->bank_id ?? ''); @endphp
                  <select class="form-control select2" name="bank_id" required>
                    <option value="" disabled {{ $selectedBank === '' ? 'selected' : '' }}>Select Bank / Cash</option>
                    <option value="0" {{ $selectedBank === '0' ? 'selected' : '' }}>Cash</option>
                    @foreach($bank as $item)
                      <option value="{{ $item->bank_id }}" {{ $selectedBank === (string) $item->bank_id ? 'selected' : '' }}>{{ $item->hname }} - {{ $item->account_title }} ({{ $item->account }})</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <p class="text-muted small mb-3">
                Hourly wage = gross salary / total working days / hours per day. Defaults come from
                <a href="{{ route('company') }}">Settings &gt; Company</a>. Temporary advances and the open fixed loan are filled in
                from the employee's Give Loan entries. The Loan amount is read-only (it always comes from those records);
                every other value can be changed.
              </p>

              <div class="table-responsive">
                <table class="table table-sm table-hover payroll-table" id="employeeTable">
                  <thead>
                    <tr>
                      <th rowspan="2"><input type="checkbox" id="selectAll" title="Select all"></th>
                      <th rowspan="2">Employee</th>
                      <th rowspan="2" class="num">Gross Salary</th>
                      <th colspan="3" class="group">Attendance (days)</th>
                      <th rowspan="2" class="num">Salary for Days</th>
                      <th colspan="3" class="group">Overtime</th>
                      <th colspan="2" class="group">Late</th>
                      <th rowspan="2" class="num">Temp. Advance</th>
                      <th colspan="3" class="group">Fixed Advance (Loan)</th>
                      <th rowspan="2" class="num">Total Salary</th>
                    </tr>
                    <tr>
                      <th class="num">Total</th>
                      <th class="num">Worked</th>
                      <th class="num">Absent</th>
                      <th class="num">Hours</th>
                      <th class="num">Rate/Hr</th>
                      <th class="num">Amount</th>
                      <th class="num">Minutes</th>
                      <th class="num">Deduction</th>
                      <th class="num">Loan</th>
                      <th class="num">Deduction</th>
                      <th class="num">Remaining</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $oldIds = old('employee_ids'); @endphp
                    @forelse($rows as $row)
                      @php
                        $id = $row->employee->employee_id;
                        $field = fn ($name, $default) => old("employees.{$id}.{$name}", $default);
                        $checked = $oldIds !== null ? in_array((string) $id, array_map('strval', $oldIds), true) : $row->included;
                      @endphp
                      <tr class="payroll-row" data-gross="{{ (float) $row->employee->salary }}">
                        <td><input type="checkbox" name="employee_ids[]" value="{{ $id }}" class="employee-checkbox" {{ $checked ? 'checked' : '' }}></td>
                        <td class="text-nowrap">
                          <strong>{{ $row->employee->name }}</strong>
                          <div class="small text-muted">{{ $row->employee->employee_no }}{{ $row->employee->designation ? ' · ' . $row->employee->designation : '' }}</div>
                        </td>
                        <td class="num">{{ number_format((float) $row->employee->salary, 2) }}</td>
                        <td class="num js-total-days"></td>
                        <td><input type="number" step="0.5" min="0" class="form-control form-control-sm js-input" data-field="actual" name="employees[{{ $id }}][actual_working_days]" value="{{ $field('actual_working_days', $row->actual_working_days !== null ? $num($row->actual_working_days) : '') }}"></td>
                        <td class="num js-absent"></td>
                        <td class="num js-salary-days"></td>
                        <td><input type="number" step="0.25" min="0" class="form-control form-control-sm js-input" data-field="ot" name="employees[{{ $id }}][overtime_hours]" value="{{ $field('overtime_hours', $num($row->overtime_hours)) }}"></td>
                        <td class="num js-ot-rate"></td>
                        <td class="num js-ot-amount"></td>
                        <td><input type="number" step="1" min="0" class="form-control form-control-sm js-input" data-field="late" name="employees[{{ $id }}][late_minutes]" value="{{ $field('late_minutes', $row->late_minutes) }}" title="Total late minutes this month"></td>
                        <td class="num js-late-amount"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm js-input" data-field="temp" name="employees[{{ $id }}][temp_advance]" value="{{ $field('temp_advance', $num($row->temp_advance)) }}"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm js-input" data-field="loan" name="employees[{{ $id }}][fixed_advance]" value="{{ $num($row->fixed_advance) }}" readonly tabindex="-1" title="Open loan from the employee's Give Loan / repayment records"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm js-input" data-field="instalment" name="employees[{{ $id }}][fixed_advance_deduction]" value="{{ $field('fixed_advance_deduction', $num($row->fixed_advance_deduction)) }}"></td>
                        <td class="num js-remaining"></td>
                        <td class="num js-net font-weight-bold"></td>
                      </tr>
                    @empty
                      <tr><td colspan="17" class="text-center text-muted">No salary employees found.</td></tr>
                    @endforelse
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="6" class="text-right">Selected employees total</th>
                      <th class="num" id="sumSalaryDays"></th>
                      <th></th><th></th>
                      <th class="num" id="sumOvertime"></th>
                      <th></th>
                      <th class="num" id="sumLate"></th>
                      <th class="num" id="sumTemp"></th>
                      <th></th>
                      <th class="num" id="sumInstalment"></th>
                      <th></th>
                      <th class="num" id="sumNet"></th>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <div class="form-group mt-3">
                <label>Notes</label>
                <textarea class="form-control" name="description" rows="2">{{ old('description', $payroll?->description) }}</textarea>
              </div>

              <div class="text-right">
                <button class="btn btn-success" type="submit">{{ $editing ? 'Update Payroll' : 'Process Payroll' }}</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  (function () {
    // Same formulas as PayrollRepository::calculate() (the server recalculates on save)
    var money = function (value) { return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var round2 = function (value) { return Math.round(value * 100) / 100; };
    var period = function (id) { return parseFloat(document.getElementById(id).value) || 0; };

    function recalc() {
      var totalDays = period('total_working_days'), hoursPerDay = period('hours_per_day') || 8;
      var otMultiplier = period('overtime_multiplier'), lateMultiplier = period('late_multiplier');
      var sums = { days: 0, ot: 0, late: 0, temp: 0, instalment: 0, net: 0 };

      document.querySelectorAll('.payroll-row').forEach(function (row) {
        var input = function (field) { var el = row.querySelector('[data-field="' + field + '"]'); return parseFloat(el.value) || 0; };
        var actualInput = row.querySelector('[data-field="actual"]');
        if (actualInput.value === '') actualInput.value = totalDays;
        var gross = parseFloat(row.dataset.gross) || 0;
        var actual = input('actual');
        var hourly = totalDays > 0 ? gross / totalDays / hoursPerDay : 0;
        var salaryDays = round2(totalDays > 0 ? gross * actual / totalDays : 0);
        var otRate = hourly * otMultiplier;
        var otAmount = round2(input('ot') * otRate);
        var lateAmount = round2(Math.round(input('late')) / 60 * hourly * lateMultiplier);
        var loan = input('loan');
        var instalment = Math.min(input('instalment'), loan);
        var net = round2(salaryDays + otAmount - lateAmount - input('temp') - instalment);

        row.querySelector('.js-total-days').textContent = totalDays;
        row.querySelector('.js-absent').textContent = round2(Math.max(0, totalDays - actual));
        row.querySelector('.js-salary-days').textContent = money(salaryDays);
        row.querySelector('.js-ot-rate').textContent = money(round2(otRate));
        row.querySelector('.js-ot-amount').textContent = money(otAmount);
        row.querySelector('.js-late-amount').textContent = money(lateAmount);
        row.querySelector('.js-remaining').textContent = money(round2(loan - instalment));
        var netCell = row.querySelector('.js-net');
        netCell.textContent = money(net);
        netCell.classList.toggle('text-danger', net < 0);

        if (row.querySelector('.employee-checkbox').checked) {
          sums.days += salaryDays; sums.ot += otAmount; sums.late += lateAmount;
          sums.temp += input('temp'); sums.instalment += instalment; sums.net += net;
        }
      });

      document.getElementById('sumSalaryDays').textContent = money(sums.days);
      document.getElementById('sumOvertime').textContent = money(sums.ot);
      document.getElementById('sumLate').textContent = money(sums.late);
      document.getElementById('sumTemp').textContent = money(sums.temp);
      document.getElementById('sumInstalment').textContent = money(sums.instalment);
      document.getElementById('sumNet').textContent = money(sums.net);
      return sums;
    }

    document.querySelectorAll('.js-input, .js-period').forEach(function (el) { el.addEventListener('input', recalc); });
    document.querySelectorAll('.employee-checkbox').forEach(function (el) { el.addEventListener('change', recalc); });
    // Changing the total working days resets attendance to full for rows that had full attendance
    var lastTotal = period('total_working_days');
    document.getElementById('total_working_days').addEventListener('change', function () {
      var total = period('total_working_days');
      document.querySelectorAll('[data-field="actual"]').forEach(function (el) {
        if ((parseFloat(el.value) || 0) === lastTotal) el.value = total;
      });
      lastTotal = total;
      recalc();
    });
    document.getElementById('selectAll').addEventListener('change', function () {
      var on = this.checked;
      document.querySelectorAll('.employee-checkbox').forEach(function (cb) { cb.checked = on; });
      recalc();
    });
    var monthSelector = document.getElementById('monthSelector');
    if (monthSelector) {
      monthSelector.addEventListener('change', function () {
        window.location.href = "{{ route('payroll.create') }}?month=" + this.value;
      });
    }
    recalc();

    // Warn before paying more than the selected account holds
    var form = document.getElementById('payrollForm');
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed) return;
      e.preventDefault();
      if (!document.querySelectorAll('.employee-checkbox:checked').length) {
        alert('Please select at least one employee');
        return;
      }
      var bankSelect = form.querySelector('select[name="bank_id"]');
      if (bankSelect.value === '') {
        alert('Please select a bank / cash account');
        return;
      }
      var total = recalc().net;
      var submit = function () { form.dataset.confirmed = '1'; form.submit(); };
      fetch("{{ url('/payroll/bank-balance') }}/" + bankSelect.value)
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.balance >= total) return submit();
          swal({
            title: 'Insufficient Balance',
            text: 'Balance in ' + bankSelect.options[bankSelect.selectedIndex].text + ' is Rs. ' + money(data.balance)
              + ', payroll needs Rs. ' + money(total) + '. Proceed anyway?',
            icon: 'warning', buttons: { cancel: 'No', confirm: 'Yes, Proceed' }, dangerMode: true
          }).then(function (ok) { if (ok) submit(); });
        })
        .catch(function () { submit(); });
    });
  })();
</script>
@endpush
@endsection

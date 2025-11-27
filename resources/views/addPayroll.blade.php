@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Create New Payroll</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                Back to Payroll
              </a>
            </div>
          </div>
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Validation Errors:</strong>
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            <!-- Month Selector -->
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Select Month</label>
                  <select class="form-control" id="monthSelector" onchange="changeMonth(this.value)">
                    @foreach($monthOptions as $monthValue => $monthLabel)
                      <option value="{{ $monthValue }}" {{ $monthValue == $selectedMonth ? 'selected' : '' }}>
                        {{ $monthLabel }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <!-- Payroll Form -->
            <form action="{{ route('payroll.store') }}" method="POST" id="payrollForm">
              @csrf
              <input type="hidden" name="month" value="{{ $selectedMonth }}">

              <!-- Bank and Payment Date Selection -->
              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Bank / Cash Account <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="" selected disabled>Select Bank / Cash</option>
                      <option value="0">Cash</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{ $item->bank_id }}">{{ $item->account_title }} ({{ $item->account }})</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="invalid-feedback">Select Bank / Cash Account</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    <div class="invalid-feedback">Payment date is required</div>
                  </div>
                </div>
              </div>

              <!-- Employees Table with Editable Salary -->
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="employeeTable" style="width:100%;">
                  <thead>
                    <tr>
                      <th style="width: 50px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                      </th>
                      <th>Sr.</th>
                      <th>Employee No</th>
                      <th>Employee Name</th>
                      <th class="text-right">Monthly Salary</th>
                      <th class="text-right">Salary Advance</th>
                      <th class="text-right">Deduct Advance</th>
                      <th class="text-right">Outstanding Loan</th>
                      <th class="text-right">Deduct Loan</th>
                      <th class="text-right">Net Payable</th>
                      <th class="text-right">Actual Salary Paid <span class="text-danger">*</span></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($payrollData as $index => $data)
                      <tr>
                        <td>
                          <input type="checkbox" name="employee_ids[]" value="{{ $data['employee']->employee_id }}" class="employee-checkbox">
                        </td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $data['employee']->employee_no }}</td>
                        <td>{{ $data['employee']->name }}</td>
                        <td class="text-right">{{ number_format($data['salary'], 2) }}</td>
                        <td class="text-right">
                          @if($data['salary_advance'] > 0)
                            <span class="badge badge-warning">{{ number_format($data['salary_advance'], 2) }}</span>
                          @else
                            -
                          @endif
                        </td>
                        <td class="text-right">
                          <input type="number" step="0.01" min="0" max="{{ $data['salary_advance'] }}" class="form-control form-control-sm text-right advance-deduction"
                                 name="advance_deductions[{{ $data['employee']->employee_id }}]"
                                 value="0"
                                 placeholder="0.00"
                                 data-employee-id="{{ $data['employee']->employee_id }}"
                                 data-salary-advance="{{ $data['salary_advance'] }}">
                        </td>
                        <td class="text-right">
                          @if($data['loans_pending'] > 0)
                            <span class="badge badge-danger">{{ number_format($data['loans_pending'], 2) }}</span>
                          @else
                            -
                          @endif
                        </td>
                        <td class="text-right">
                          <input type="number" step="0.01" min="0" max="{{ $data['loans_pending'] }}" class="form-control form-control-sm text-right loan-deduction"
                                 name="loan_deductions[{{ $data['employee']->employee_id }}]"
                                 value="0"
                                 placeholder="0.00"
                                 data-employee-id="{{ $data['employee']->employee_id }}"
                                 data-loans-pending="{{ $data['loans_pending'] }}">
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($data['net_salary'], 2) }}</td>
                        <td class="text-right">
                          <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right salary-input"
                                 name="salary_amounts[{{ $data['employee']->employee_id }}]"
                                 value="{{ $data['net_salary'] }}"
                                 placeholder="0.00"
                                 data-employee-id="{{ $data['employee']->employee_id }}"
                                 data-base-salary="{{ $data['net_salary'] }}">
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="11" class="text-center">No salary employees found</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- Action Buttons -->
              <div class="form-group row mt-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-success" type="submit" id="processBtn">
                    Process Payroll
                  </button>
                  <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function changeMonth(month) {
    window.location.href = "{{ route('payroll.create') }}?month=" + month;
  }

  function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
  }

  // Update salary amount when deductions change
  function updateSalaryAmount(employeeId) {
    const baseSalary = parseFloat(document.querySelector(`[data-employee-id="${employeeId}"][data-base-salary]`).dataset.baseSalary) || 0;
    const advanceDeduction = parseFloat(document.querySelector(`[data-employee-id="${employeeId}"].advance-deduction`).value) || 0;
    const loanDeduction = parseFloat(document.querySelector(`[data-employee-id="${employeeId}"].loan-deduction`).value) || 0;

    const salaryInput = document.querySelector(`[data-employee-id="${employeeId}"].salary-input`);
    const newSalary = baseSalary + advanceDeduction + loanDeduction;
    salaryInput.value = newSalary.toFixed(2);
  }

  // Calculate total payroll amount
  function calculateTotalPayroll() {
    let total = 0;
    document.querySelectorAll('.salary-input').forEach(input => {
      const value = parseFloat(input.value) || 0;
      total += value;
    });
    return total;
  }

  // Add event listeners for deduction inputs
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.advance-deduction, .loan-deduction').forEach(input => {
      input.addEventListener('change', function() {
        const employeeId = this.dataset.employeeId;
        updateSalaryAmount(employeeId);
      });
    });
  });

  // Handle form submission
  document.getElementById('payrollForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    const bankSelect = document.querySelector('select[name="bank_id"]');

    if (checkboxes.length === 0) {
      e.preventDefault();
      alert('Please select at least one employee');
      return false;
    }

    // Check for empty string specifically (not "0" which is valid for cash)
    if (bankSelect.value === '') {
      e.preventDefault();
      alert('Please select a bank/cash account');
      return false;
    }

    // Check bank balance
    e.preventDefault();
    const totalPayroll = calculateTotalPayroll();
    const bankId = bankSelect.value;
    const bankName = bankSelect.options[bankSelect.selectedIndex].text;

    // Fetch bank balance
    const baseUrl = "{{ url('/payroll/bank-balance') }}";
    fetch(baseUrl + "/" + bankId)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        const balance = data.balance;

        if (balance < totalPayroll) {
          // Insufficient balance - show SweetAlert confirmation
          swal({
            title: 'Insufficient Balance',
            text: 'Insufficient balance in ' + bankName + '. Current balance: Rs. ' +
                  new Intl.NumberFormat('en-PK').format(Math.abs(balance)) + ', Required: Rs. ' +
                  new Intl.NumberFormat('en-PK').format(totalPayroll) + '. Do you want to proceed anyway?',
            icon: 'warning',
            buttons: {
              cancel: 'No',
              confirm: 'Yes, Proceed'
            },
            dangerMode: true,
          })
          .then((willProceed) => {
            if (willProceed) {
              // User confirmed - submit the form
              document.getElementById('payrollForm').submit();
            }
          });
        } else {
          // Sufficient balance - submit the form
          document.getElementById('payrollForm').submit();
        }
      })
      .catch(error => {
        console.error('Error fetching balance:', error);
        // On error, show alert and allow user to decide
        swal({
          title: 'Warning',
          text: 'Could not verify bank balance. Do you want to proceed anyway?',
          icon: 'warning',
          buttons: {
            cancel: 'No',
            confirm: 'Yes, Proceed'
          },
          dangerMode: true,
        })
        .then((willProceed) => {
          if (willProceed) {
            document.getElementById('payrollForm').submit();
          }
        });
      });
  });
</script>
@endsection


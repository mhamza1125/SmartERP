@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Payroll - {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                Back to Payroll
              </a>
            </div>
          </div>
          <div class="card-body">
            @if(session('warning'))
              <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

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

            <!-- Payroll Form -->
            <form action="{{ route('payroll.update', $selectedMonth) }}" method="POST" id="payrollForm">
              @csrf
              @method('PUT')
              <input type="hidden" name="month" value="{{ $selectedMonth }}">

              <!-- Bank and Payment Date Selection -->
              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Bank / Cash Account <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="bank_id" required>
                      <option value="" selected disabled>Select Bank / Cash</option>
                      <option value="0" {{ $selectedBankId === 0 ? 'selected' : '' }}>Cash</option>
                      @if($bank->count())
                        @foreach($bank as $item)
                          <option value="{{ $item->bank_id }}" {{ $selectedBankId == $item->bank_id ? 'selected' : '' }}>{{ $item->account_title }} ({{ $item->account }})</option>
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
                          <input type="checkbox" name="employee_ids[]" value="{{ $data['employee']->employee_id }}" class="employee-checkbox" {{ $data['is_paid'] ? 'checked' : '' }}>
                        </td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $data['employee']->employee_no }}</td>
                        <td>{{ $data['employee']->name }}</td>
                        <td class="text-right">{{ number_format($data['salary'], 2) }}</td>
                        <td class="text-right">
                          @if($data['loans_pending'] > 0)
                            <span class="badge badge-danger">{{ number_format($data['loans_pending'] + $data['loan_deduction_amount'], 2) }}</span>
                          @else
                            -
                          @endif
                        </td>
                        <td class="text-right">
                          <input type="number" step="0.01" min="0" max="{{ $data['loans_pending'] + $data['loan_deduction_amount'] }}" class="form-control form-control-sm text-right loan-deduction"
                                 name="loan_deductions[{{ $data['employee']->employee_id }}]"
                                 value="{{ $data['loan_deduction_amount'] }}"
                                 placeholder="0.00"
                                 data-employee-id="{{ $data['employee']->employee_id }}"
                                 data-loans-pending="{{ $data['loans_pending'] }}">
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($data['salary'] - $data['loans_pending'], 2) }}</td>
                        <td class="text-right">
                          <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right salary-input"
                                 name="salary_amounts[{{ $data['employee']->employee_id }}]"
                                 value="{{ $data['paid_amount'] > 0 ? $data['paid_amount'] : $data['salary'] }}"
                                 placeholder="0.00"
                                 data-employee-id="{{ $data['employee']->employee_id }}"
                                 data-base-salary="{{ $data['salary'] }}">
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="9" class="text-center">No salary employees found</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- Action Buttons -->
              <div class="form-group row mt-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-success" type="submit" id="processBtn">
                    Update Payroll
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
  function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
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

  // Handle form submission
  const payrollFormHandler = function(e) {
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
              // User confirmed - submit the form by removing the event listener temporarily
              const form = document.getElementById('payrollForm');
              form.removeEventListener('submit', payrollFormHandler);
              form.submit();
            }
          });
        } else {
          // Sufficient balance - submit the form by removing the event listener temporarily
          const form = document.getElementById('payrollForm');
          form.removeEventListener('submit', payrollFormHandler);
          form.submit();
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
            // User confirmed - submit the form by removing the event listener temporarily
            const form = document.getElementById('payrollForm');
            form.removeEventListener('submit', payrollFormHandler);
            form.submit();
          }
        });
      });
  };

  document.getElementById('payrollForm').addEventListener('submit', payrollFormHandler);
</script>
@endsection


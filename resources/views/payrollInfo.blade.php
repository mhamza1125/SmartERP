@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Payroll Details - {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.edit', $selectedMonth) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                Back to Payroll
              </a>
            </div>
          </div>
          <div class="card-body">
            <!-- Summary Section -->
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="form-group">
                  <label><strong>Month</strong></label>
                  <p>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label><strong>Processing Date</strong></label>
                  <p>{{ \Carbon\Carbon::parse($processingDate)->format('d M Y') }}</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label><strong>Bank / Cash Account</strong></label>
                  <p>{{ $bankName }}</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label><strong>Total Amount</strong></label>
                  <p><strong>Rs. {{ number_format($totalAmount, 2) }}</strong></p>
                </div>
              </div>
            </div>

            <!-- Employees Table -->
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="payrollDetailsTable" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Employee No</th>
                    <th>Employee Name</th>
                    <th class="text-right">Monthly Salary</th>
                    <th class="text-right">Salary Advance</th>
                    <th class="text-right">Pending Loans</th>
                    <th class="text-right">Net Payable</th>
                    <th class="text-right">Actual Salary Paid</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($payrollData as $index => $data)
                    <tr>
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
                        @if($data['loans_pending'] > 0)
                          <span class="badge badge-danger">{{ number_format($data['loans_pending'], 2) }}</span>
                        @else
                          -
                        @endif
                      </td>
                      <td class="text-right font-weight-bold">{{ number_format($data['net_salary'], 2) }}</td>
                      <td class="text-right font-weight-bold">{{ number_format($data['paid_amount'], 2) }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center">No payroll data found</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  $(document).ready(function() {
    $('#payrollDetailsTable').DataTable({
      "order": [[0, "asc"]],
      "pageLength": 25,
      "columnDefs": [
        { "orderable": false, "targets": [1, 2] }
      ]
    });
  });
</script>
@endsection


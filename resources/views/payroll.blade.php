@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Payroll History</h4>
            <div class="card-header-action">
              <a href="{{ route('payroll.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Payroll
              </a>
            </div>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            @if(session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            <!-- Payroll Batches Table -->
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="payrollTable" style="width:100%;">
                <thead>
                  <tr>
                    <th>Month</th>
                    <th>Processing Date</th>
                    <th class="text-right">Employees Paid</th>
                    <th class="text-right">Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($payrollBatches as $batch)
                    <tr>
                      <td>
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $batch->month)->format('F Y') }}</strong>
                      </td>
                      <td>{{ \Carbon\Carbon::parse($batch->processing_date)->format('d M Y') }}</td>
                      <td class="text-right">{{ $batch->employee_count }}</td>
                      <td class="text-right">{{ number_format($batch->total_amount, 2) }}</td>
                      <td>
                        <span class="badge badge-success">{{ $batch->status }}</span>
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="{{ route('payroll.info', $batch->month) }}" class="btn btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="{{ route('payroll.edit', $batch->month) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center">No payroll batches found. <a href="{{ route('payroll.create') }}">Create one now</a></td>
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
    $('#payrollTable').DataTable({
      "order": [[0, "desc"]],
      "pageLength": 25,
      "columnDefs": [
        { "orderable": false, "targets": 5 }
      ]
    });
  });
</script>
@endsection


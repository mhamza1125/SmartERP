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
              @if(auth()->user()->can('payroll_create', App\Models\User::class))<a href="{{ route('payroll.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Payroll
              </a>@endif
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
              <table class="table table-striped table-hover" id="tableExport" data-order='[[0, "desc"]]' style="width:100%;">
                <thead>
                  <tr>
                    <th>Month</th>
                    <th>Payment Date</th>
                    <th class="text-right">Working Days</th>
                    <th class="text-right">Employees</th>
                    <th class="text-right">Total Payable</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($payrollBatches as $batch)
                    <tr>
                      <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $batch->month)->format('F Y') }}</strong></td>
                      <td>{{ formatDate($batch->payment_date) }}</td>
                      <td class="text-right">{{ $batch->total_working_days !== null ? rtrim(rtrim(number_format($batch->total_working_days, 2), '0'), '.') : '-' }}</td>
                      <td class="text-right">{{ $batch->employee_count }}</td>
                      <td class="text-right">{{ number_format($batch->total_amount, 2) }}</td>
                      <td>
                        @if($batch->legacy)
                          <span class="badge badge-light border" title="Processed before salary breakdowns; open Edit and save to add working days, overtime and advances">No breakdown</span>
                        @else
                          <span class="badge badge-success">Processed</span>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          @unless($batch->legacy)
                            @if(auth()->user()->can('payroll_show', App\Models\User::class))<a href="{{ route('payroll.info', $batch->month) }}" class="btn btn-sm btn-info" title="View details"><i class="fas fa-eye"></i></a>@endif
                            @if(auth()->user()->can('payroll_show', App\Models\User::class))<a href="{{ route('payroll.print', $batch->month) }}" class="btn btn-sm btn-outline-info" title="Print payroll" target="_blank"><i class="fas fa-print"></i></a>@endif
                          @endunless
                          @can('payroll_edit', App\Models\User::class)<a href="{{ route('payroll.edit', $batch->month) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>@endcan
@can('payroll_delete', App\Models\User::class)@include('deleteButton', ['url' => route('payroll.destroy', $batch->month), 'check' => route('payroll.deleteCheck', $batch->month), 'label' => 'Payroll '.$batch->month, 'class' => 'btn btn-sm btn-danger', 'text' => '<i class="fas fa-trash"></i>'])@endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center">No payroll processed yet. @if(auth()->user()->can('payroll_create', App\Models\User::class))<a href="{{ route('payroll.create') }}">Create one now</a>@endif</td>
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

@endsection


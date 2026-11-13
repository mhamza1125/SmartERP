{{-- TEMPORARY one-time cleanup page (Settings > Reset Test Data) - remove with DataResetController --}}
@extends('index')
@section('content')
    <section class="section">
        <div class="section-body">
            <h4 class="mb-3">Reset Test / Transactional Data</h4>
            @if ($result)
                <div class="card">
                    <div class="card-header"><h4 class="text-success">Reset completed</h4></div>
                    <div class="card-body">
                        <p>
                            {{ number_format($result['total_deleted']) }} records deleted in {{ $result['seconds'] }} s;
                            {{ $result['opening_balance_amounts_zeroed'] }} opening-balance amounts set to zero;
                            {{ $result['files_removed'] }} attachment files removed. Verification passed: every master table kept all its rows.
                        </p>
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Table</th><th class="text-right">Deleted</th></tr></thead>
                            <tbody>
                                @foreach ($result['deleted'] as $table => $count)
                                    <tr><td>{{ $table }}</td><td class="text-right">{{ number_format($count) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="alert alert-danger">
                <h5 class="mb-2">Are you sure you want to reset the transactional and testing data?</h5>
                <p class="mb-2">
                    This operation will permanently delete existing stock records, purchases, PTCs, material processing records,
                    orders, deliveries, financial transactions, payroll records, and other associated operational data.
                    All deleted transactional and financial records will be permanently removed and cannot be recovered through the application.
                </p>
                <p class="mb-0"><strong>Take a database backup before continuing.</strong> The figures below are a dry run: nothing has been changed yet.</p>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h4 class="text-danger">Will be permanently deleted ({{ number_format($preview['total']) }} records)</h4></div>
                        <div class="card-body">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Area</th><th>Records</th><th class="text-right">Count</th></tr></thead>
                                <tbody>
                                    @foreach ($preview['steps'] as $step)
                                        <tr>
                                            <td>{{ $step['group'] }}</td>
                                            <td>{{ $step['label'] }} <small class="text-muted">({{ $step['table'] }})</small></td>
                                            <td class="text-right">{{ number_format($step['count']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="text-muted mb-0">
                                Includes {{ number_format($preview['opening_stock_lines']) }} opening-stock lines: product and material stock is
                                calculated from these records, so every stock balance starts at zero afterwards. Attendance is read live from the
                                attendance machine file and has no stored records here, so it is not affected.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h4 class="text-success">Will be preserved</h4></div>
                        <div class="card-body">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Master data</th><th class="text-right">Rows kept</th></tr></thead>
                                <tbody>
                                    @foreach ($preview['preserved'] as $row)
                                        <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ number_format($row['count']) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>Confirm</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('data-reset.run') }}" id="dataResetForm">
                        @csrf
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="hidden" name="reset_opening_balances" value="0">
                                <input type="checkbox" class="custom-control-input" id="reset_opening_balances" name="reset_opening_balances" value="1"
                                    {{ old('reset_opening_balances', '1') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="reset_opening_balances">
                                    Also set every opening balance to zero
                                    ({{ $preview['opening_balances']['rows'] }} entries kept as rows, {{ $preview['opening_balances']['non_zero'] }} currently carry an amount).
                                    Untick to keep the current opening balances of customers, vendors, contractors and employees.
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="understand" name="understand" value="1">
                                <label class="custom-control-label" for="understand">I understand the records listed above will be permanently deleted.</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="backup_taken" name="backup_taken" value="1">
                                <label class="custom-control-label" for="backup_taken">A backup of the database has been taken.</label>
                            </div>
                        </div>
                        <div class="form-group" style="max-width: 320px">
                            <label for="confirm_phrase">Type <strong>{{ $phrase }}</strong> to confirm</label>
                            <input type="text" class="form-control" id="confirm_phrase" name="confirm_phrase" autocomplete="off">
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-warning">{{ implode(' ', $errors->all()) }}</div>
                        @endif
                        <button type="submit" class="btn btn-danger" id="dataResetSubmit">Reset Test Data</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.getElementById('dataResetForm').addEventListener('submit', function (e) {
        var form = this;
        if (form.dataset.confirmed) {
            return;
        }
        e.preventDefault();
        swal({
            title: 'Delete all test / transactional data?',
            text: 'This cannot be undone through the application. Do you want to proceed?',
            icon: 'warning',
            buttons: ['Cancel', 'Yes, reset the data'],
            dangerMode: true
        }).then(function (ok) {
            if (ok) {
                form.dataset.confirmed = '1';
                document.getElementById('dataResetSubmit').disabled = true;
                form.submit();
            }
        });
    });
</script>
@endpush

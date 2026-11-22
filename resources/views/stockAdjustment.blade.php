{{-- Stock adjustments list (StockAdjustmentController::index) --}}
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Stock Adjustments</h4>
            <div class="card-header-action">
              @can('create', App\Models\StockAdjustment::class)<a href="{{ route('stockAdjustment.add') }}" class="btn btn-primary">Add Adjustment</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <p class="text-muted small">
              Increase or decrease material or product stock outside the normal documents (stock count corrections, damage, found stock...).
              Products can be adjusted at any stage, including Rejection with the stage the pieces were rejected at.
            </p>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Adjustment No</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-right">Lines</th>
                    <th class="text-right">Total QTY</th>
                    <th>Order</th>
                    <th>Reason</th>
                    <th>By</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($adjustments as $adjustment)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $adjustment->stock_no }}</td>
                      <td data-order="{{ $adjustment->stock_date }}">{{ formatDate($adjustment->stock_date) }}</td>
                      <td>
                        @if((int) $adjustment->stock_type === 1)
                          <span class="badge badge-success">Increase</span>
                        @else
                          <span class="badge badge-warning">Decrease</span>
                        @endif
                      </td>
                      <td class="text-right">{{ $adjustment->lines }}</td>
                      <td class="text-right">{{ rtrim(rtrim(number_format((float) $adjustment->quantity, 2), '0'), '.') }}</td>
                      <td>{{ $adjustment->job_no ?? '-' }}</td>
                      <td>{{ \Illuminate\Support\Str::limit($adjustment->description, 60) }}</td>
                      <td>{{ $adjustment->user_name ?? '-' }}</td>
                      <td class="text-nowrap">
                        @can('show', App\Models\StockAdjustment::class)<a href="{{ route('stockAdjustment.show', $adjustment->stock_id) }}" class="btn btn-primary btn-sm">View</a>@endcan
                        @can('delete', App\Models\StockAdjustment::class)@include('deleteButton', ['url' => route('stockAdjustment.destroy', $adjustment->stock_id), 'check' => route('stockAdjustment.deleteCheck', $adjustment->stock_id), 'label' => 'Stock adjustment '.$adjustment->stock_no])@endcan
                      </td>
                    </tr>
                  @endforeach
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

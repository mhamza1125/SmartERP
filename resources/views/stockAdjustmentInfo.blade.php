{{-- One stock adjustment (StockAdjustmentController::show) --}}
@extends('index')
@section('content')
@php
  $increase = (int) $adjustment->stock_type === 1;
  $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 4), '0'), '.');
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Stock Adjustment {{ $adjustment->stock_no }}</h4>
            <div class="card-header-action">
              <a href="{{ route('stockAdjustment') }}" class="btn btn-primary">Back</a>
              @can('delete', App\Models\StockAdjustment::class)@include('deleteButton', ['url' => route('stockAdjustment.destroy', $adjustment->stock_id), 'check' => route('stockAdjustment.deleteCheck', $adjustment->stock_id), 'label' => 'Stock adjustment '.$adjustment->stock_no, 'class' => 'btn btn-danger'])@endcan
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Adjustment No:</b> {{ $adjustment->stock_no }}</td>
                  <td><b>Date:</b> {{ formatDate($adjustment->stock_date) }}</td>
                  <td><b>Type:</b>
                    @if($increase)<span class="badge badge-success">Increase (+)</span>@else<span class="badge badge-warning">Decrease (-)</span>@endif
                  </td>
                </tr>
                <tr>
                  <td><b>Order:</b> {{ $adjustment->job_no ?? ($increase ? '-' : 'Free stock only') }}</td>
                  <td><b>Entered By:</b> {{ $adjustment->user_name ?? '-' }}</td>
                  <td><b>Entered On:</b> {{ formatDateTime($adjustment->created_at) }}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Reason:</b> {{ $adjustment->description }}</td>
                </tr>
              </tbody>
            </table>

            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Item</th>
                    <th>Stage</th>
                    <th class="text-right">{{ $increase ? 'Added' : 'Removed' }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($lines as $line)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        @if($line->material_id)
                          Material: {{ $line->material_no }} - {{ $line->material_name }}
                        @else
                          {{ $line->article_no }} - {{ $line->product_name }} (Size {{ $line->size_name }})
                        @endif
                      </td>
                      <td>
                        @if($line->material_id)
                          -
                        @elseif((int) $line->stage_id === \App\Models\Stock::STAGE_REJECTED)
                          <span class="badge badge-danger">Rejection</span> rejected at {{ $line->rejected_stage_name ?? 'stage not recorded' }}
                        @else
                          {{ $line->stage_name }}
                        @endif
                      </td>
                      <td class="text-right">{{ $increase ? '+' : '-' }}{{ $qty($line->quantity) }} {{ $line->material_id ? $line->material_unit : $line->product_unit }}</td>
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

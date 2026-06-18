@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Process Travel Cards (PTC)</h4>
            <div class="card-header-action">
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPtcModal">
                <i class="fas fa-plus"></i> Create PTC
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>PTC No</th>
                    <th>Order</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Current Stage</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($ptcList->count())
                    @foreach($ptcList as $item)
                    @php
                      // Extract quantity from description [QTY:X] format
                      $ptcQty = 1;
                      if (preg_match('/\[QTY:(\d+)\]/', $item->description ?? '', $matches)) {
                          $ptcQty = $matches[1];
                      }
                    @endphp
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{ $item->stock_date ? \Carbon\Carbon::parse($item->stock_date)->format('d-m-Y') : 'N/A' }}</td>
                      <td>PTC-{{$item->stock_no}}</td>
                      <td>{{$item->job_no ?? 'Default PTC'}}</td>
                      <td>{{$item->product_name}} - {{$item->size_name}}</td>
                      <td>{{$ptcQty}}</td>
                      <td>{{$item->current_stage_name ?? '-'}}</td>
                      <td>
                        @if($item->stock_status == 6)
                          <span class="badge badge-warning">In Progress</span>
                        @elseif($item->stock_status == 7)
                          <span class="badge badge-success">Completed</span>
                        @else
                          <span class="badge badge-secondary">Unknown</span>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('ptc.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                        {{-- @if($item->stock_status == 6)
                          <a href="{{ route('ptc.move.form', $item->stock_id) }}" class="btn btn-success btn-sm">Move Stage</a>
                        @endif --}}
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Create PTC Modal -->
<div class="modal fade" id="createPtcModal" tabindex="-1" role="dialog" aria-labelledby="createPtcModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createPtcModalLabel">Create New PTC</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="ptcModalForm" action="{{ route('ptc.create') }}" method="GET">
        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="d-block">Order Selection</label>
                <select class="form-control select2 w-100" id="modal_order_id" name="order_id" style="width: 100%;">
                  <option value="" disabled selected>Select Order</option>
                  <option value="0">Default PTC (No Order)</option>
                  @if($orders->count())
                    @foreach($orders as $order)
                      <option value="{{$order->order_id}}">{{$order->job_no}}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="d-block">Product <span class="text-danger">*</span></label>
                <select class="form-control select2 w-100" id="modal_product_type_id" name="product_type_id" required style="width: 100%;">
                  <option value="" disabled selected>Select Product</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="d-block">Start Stage <span class="text-danger">*</span></label>
                <select class="form-control select2 w-100" id="modal_start_stage_id" name="start_stage_id" required style="width: 100%;">
                  <option value="" disabled selected>Select Start Stage</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="d-block">End Stage <span class="text-danger">*</span></label>
                <select class="form-control select2 w-100" id="modal_end_stage_id" name="end_stage_id" required style="width: 100%;">
                  <option value="" disabled selected>Select End Stage</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Continue to PTC Form</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var ajaxPtcProductsUrl = "{{ route('ajaxPtcProducts') }}";
var ajaxPtcStagesUrl = "{{ route('ajaxPtcStages') }}";
</script>
@endsection


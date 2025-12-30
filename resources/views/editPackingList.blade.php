@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Packing List</h4>
            <div class="card-header-action">
              <a href="{{ route('packingList.show', $packingList->packing_list_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <!-- Delivery Info -->
            <div class="row mb-4">
              <div class="col-md-6">
                <table class="table table-sm table-borderless">
                  <tbody>
                    <tr><td><b>Delivery No:</b> {{ $delivery->stock_no }}</td></tr>
                    <tr><td><b>Order No:</b> {{ $delivery->order_no }}</td></tr>
                    <tr><td><b>Customer:</b> {{ $delivery->fname }} {{ $delivery->lname }}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

            <form id="packingListForm" action="{{ route('packingList.update', $packingList->packing_list_id) }}" method="POST">
              @csrf

              <!-- Pallet Information Section -->
              <div class="row mb-4">
                <div class="col-12">
                  <div class="card border-info">
                    <div class="card-header bg-info text-white">
                      <h6 class="mb-0">Pallet Information</h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-3">
                          <label>Pallet Quantity</label>
                          <input type="number" class="form-control" name="pallet_qty" min="1" placeholder="e.g. 10" value="{{ $packingList->pallet_qty ?? '' }}">
                        </div>
                        <div class="col-md-4">
                          <label>Pallet Weight (kg)</label>
                          <input type="number" class="form-control" name="pallet_weight" min="0" step="0.01" placeholder="e.g. 4.32" value="{{ $packingList->pallet_weight ?? '' }}">
                        </div>
                        <div class="col-md-5">
                          <label>Pallet Dimension</label>
                          <input type="text" class="form-control" name="pallet_dimension" placeholder="e.g. 100x120 cm" value="{{ $packingList->pallet_dimension ?? '' }}">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Product Split Summary Section -->
              {{-- <div class="row mb-3">
                <div class="col-12">
                  <div class="alert alert-info" id="splitSummary" style="display: none;">
                    <h6 class="alert-heading">Product Allocation Summary</h6>
                    <div id="splitSummaryContent"></div>
                  </div>
                </div>
              </div> --}}

              <!-- Carton Groups Section -->
              <div class="row">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0">Carton Groups</h6>
                      <small class="text-muted d-block mt-1 ml-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Drag products between groups to split across multiple cartons.</strong>
                        {{-- The same product can appear in multiple groups with different quantities.
                        Total allocated pieces cannot exceed the delivered quantity. --}}
                      </small>
                    </div>
                    <div class="card-body" id="cartonGroupsContainer">
                      <!-- Existing carton groups will be loaded here -->
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-12">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Packing List
                  </button>
                  <a href="{{ route('packingList.show', $packingList->packing_list_id) }}" class="btn btn-secondary">Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.carton-group {
  padding: 15px;
  margin-bottom: 15px;
  background: #fff;
  border: 2px solid #6777ef;
  border-radius: 6px;
}

.carton-group-header {
  background: #6777ef;
  color: white;
  padding: 8px 12px;
  margin: -15px -15px 15px -15px;
  border-radius: 4px 4px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.products-zone {
  min-height: 50px;
  padding: 10px;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 4px;
  margin-top: 10px;
}

.products-zone.drag-over {
  background: #e3f2fd;
  border-color: #6777ef;
}

.product-item-row {
  padding: 10px;
  margin-bottom: 8px;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  cursor: move;
  transition: all 0.2s;
}

.product-item-row:hover {
  background: #f8f9fa;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-item-row.dragging {
  opacity: 0.5;
}

.drag-handle {
  cursor: move;
  display: inline-block;
}

.drag-handle:hover {
  color: #6777ef;
}
</style>

<!-- Pass existing cartons data to JavaScript -->
<script>
  var existingCartons = @json($cartons);
  var deliveryProducts = [];
</script>

<script src="{{ URL::asset('assets/js/packing-list.js') }}"></script>
@endsection

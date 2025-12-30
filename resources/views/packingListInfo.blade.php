@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Packing List Info</h4>
            <div class="card-header-action">
              <a href="{{ route('packingList.print', $packingList->order_id) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Print
              </a>
              <a href="{{ route('packingList.edit', $packingList->packing_list_id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="{{ route('delivery.show', $packingList->delivery_id) }}" class="btn btn-primary">Back to Delivery</a>
            </div>
          </div>
          <div class="card-body">
            <!-- Packing List Header Info -->
            <div class="row mb-4">
              <div class="col-md-6">
                <table class="table table-sm table-borderless">
                  <tbody>
                    {{-- <tr><td><b>Stock No:</b> {{ $packingList->stock_no }}</td></tr> --}}
                    <tr><td><b>Order No:</b> {{ $packingList->order_no }}</td></tr>
                    {{-- <tr><td><b>Job No:</b> {{ $packingList->job_no }}</td></tr> --}}
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-sm table-borderless">
                  <tbody>
                    <tr><td><b>Customer:</b> {{ $packingList->fname }} {{ $packingList->lname }}</td></tr>
                    {{-- <tr><td><b>Customer No:</b> {{ $packingList->customer_no }}</td></tr> --}}
                    {{-- <tr><td><b>Created:</b> {{ \Carbon\Carbon::parse($packingList->created_at)->format('d M Y, h:i A') }}</td></tr> --}}
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pallet Information -->
            @if($packingList->pallet_qty || $packingList->pallet_weight || $packingList->pallet_dimension)
            <div class="row mb-4">
              <div class="col-12">
                <div class="card border-info">
                  <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Pallet Information</h6>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      @if($packingList->pallet_qty)
                      <div class="col-md-3">
                        <p><strong>Pallet Quantity:</strong> {{ $packingList->pallet_qty }}</p>
                      </div>
                      @endif
                      @if($packingList->pallet_weight)
                      <div class="col-md-3">
                        <p><strong>Weight per Pallet:</strong> {{ $packingList->pallet_weight }} kg</p>
                      </div>
                      @endif
                      @if($packingList->pallet_dimension)
                      <div class="col-md-6">
                        <p><strong>Pallet Dimension:</strong> {{ $packingList->pallet_dimension }}</p>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endif

            <!-- Carton Groups -->
            <div class="row">
              <div class="col-12">
                <h5 class="mb-3">Carton Groups</h5>
                @foreach($cartons as $index => $carton)
                <div class="card mb-3">
                  <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                      <h6 class="mb-0">
                        @if($carton->carton_from == $carton->carton_to)
                          Carton #{{ $carton->carton_from }}
                        @else
                          Cartons #{{ $carton->carton_from }} - #{{ $carton->carton_to }}
                        @endif
                        <span class="badge badge-light text-dark ml-2">{{ $carton->carton_to - $carton->carton_from + 1 }} carton(s)</span>
                      </h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <!-- Box Dimension and Weight -->
                    @if($carton->box_dimension || $carton->box_weight)
                    <div class="row mb-3">
                      @if($carton->box_dimension)
                      <div class="col-md-6">
                        <p class="mb-1"><strong>Box Dimension:</strong> {{ $carton->box_dimension }}</p>
                      </div>
                      @endif
                      @if($carton->box_weight)
                      <div class="col-md-6">
                        <p class="mb-1"><strong>Weight per Box:</strong> {{ $carton->box_weight }} kg</p>
                      </div>
                      @endif
                    </div>
                    <hr class="my-2">
                    @endif

                    <table class="table table-sm table-bordered">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Product Name</th>
                          <th>Pcs Each Carton</th>
                          <th>Total Pcs</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($carton->items as $itemIndex => $item)
                        <tr>
                          <td>{{ $itemIndex + 1 }}</td>
                          <td>{{ $item->article_no }}</td>
                          <td>{{ $item->name }}</td>
                          <td>{{ $item->pcs_each_carton }}</td>
                          <td><strong>{{ $item->pcs_each_carton * ($carton->carton_to - $carton->carton_from + 1) }}</strong></td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr class="table-active">
                          <td colspan="4" class="text-right"><strong>Group Total:</strong></td>
                          <td><strong>{{ $carton->items->sum(function($item) use ($carton) { return $item->pcs_each_carton * ($carton->carton_to - $carton->carton_from + 1); }) }}</strong></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
                @endforeach

                @if($cartons->count() == 0)
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> No carton groups found.
                </div>
                @endif
              </div>
            </div>

            <!-- Grand Total -->
            @if($cartons->count() > 0)
            <div class="row">
              <div class="col-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <h5>Summary</h5>
                        <p class="mb-1"><strong>Total Carton Groups:</strong> {{ $cartons->count() }}</p>
                        <p class="mb-1"><strong>Total Cartons:</strong> {{ $cartons->sum(function($c) { return $c->carton_to - $c->carton_from + 1; }) }}</p>
                      </div>
                      <div class="col-md-6 text-right">
                        <h5>Grand Total Pieces</h5>
                        <h3 class="text-black">
                          {{ $cartons->sum(fn($c) => $c->items->sum(fn($item) => $item->pcs_each_carton * ($c->carton_to - $c->carton_from + 1))) }}
                        </h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

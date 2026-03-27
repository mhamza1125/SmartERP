@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Return Details @if(isset($isMultiOrder) && $isMultiOrder) <span class="badge badge-secondary ml-2">Multi-Order</span> @endif</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('delivery-return.print', $return->delivery_return_id) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('delivery-return') }}" class="btn btn-primary">Back to Returns</a>
                <a href="{{ route('delivery-return.edit', $return->delivery_return_id) }}" class="btn btn-warning">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer Name:</b> {{ $return->fname }} {{ $return->lname }}</td></tr>
                    @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
                    {{-- <tr><td><b>Primary Order:</b> {{ $return->order_no }}</td></tr> --}}
                    <tr><td><b>All Orders:</b>
                      @foreach($relatedOrders as $index => $order)
                        @if($index < 5)
                        <span class="badge badge-secondary mr-1 mb-1">{{$order->order_no ?? 'N/A'}}</span>
                        @endif
                      @endforeach
                      @if(count($relatedOrders) > 5)
                      <span class="badge badge-light">+{{count($relatedOrders) - 5}} more</span>
                      @endif
                    </td></tr>
                    @else
                    <tr><td><b>Order No:</b> {{ $return->order_no }}</td></tr>
                    <tr><td><b>Job No:</b> {{ $return->job_no }}</td></tr>
                    @endif
                    <tr><td><b>Delivery Date:</b> {{ \Carbon\Carbon::parse($return->stock_date)->format('d-m-Y') }}</td></tr>
                    @php $description = $return->return_description; @endphp
                    @if($description)<tr><td><b>Detail:</b></td></tr>
                    <tr><td>{{ $description }}</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Delivery No:</b> {{ is_array($deliveryInfo) ? ($deliveryInfo['delivery_no'] ?? 'N/A') : ($deliveryInfo->delivery_no ?? 'N/A') }}</td></tr>
                    <tr><td><b>Return No:</b> {{ $return->return_no }}</td></tr>
                    {{-- <tr><td><b>Stock No:</b> {{ $return->stock_no }}</td></tr> --}}
                    <tr><td><b>Return Date:</b> {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td></tr>
                    <tr><td><b>Return Reason:</b> {{ $return->return_reason }}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article</th>
                      <th>Item / Product</th>
                      <th>Product Stage</th>
                      <th>Size</th>
                      <th>Unit</th>
                      <th>Original Qty</th>
                      <th>Returned Qty</th>
                      <th>Reason</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($returnItems->count())
                      @foreach($returnItems as $item)
                        <tr>
                          <td>{{ $loop->index + 1 }}</td>
                          <td>{{ $item->article_no ?? 'N/A' }}</td>
                          <td>
                            @if($item->product_name)
                              {{ $item->product_name }}
                            @else
                              {{ $item->material_name }}
                            @endif
                          </td>
                          <td>{{ $item->stage_name ?? 'N/A' }}</td>
                          <td>{{ $item->size_name ?? 'N/A' }}</td>
                          <td>{{ $item->product_unit ?? $item->material_unit ?? 'N/A' }}</td>
                          <td>{{ number_format($item->original_quantity) }}</td>
                          <td>{{ number_format($item->quantity) }}</td>
                          <td>{{ $item->reason ?? 'N/A' }}</td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="9" class="text-center">No items have been returned for this delivery return record.</td>
                      </tr>
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article</th>
                      <th>Item / Product</th>
                      <th>Product Stage</th>
                      <th>Size</th>
                      <th>Unit</th>
                      <th>Original Qty</th>
                      <th>Returned Qty</th>
                      <th>Reason</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            @if($returnItems->count())
            <div class="row mt-3">
              <div class="col-md-12">
                <div class="alert alert-info">
                  <h6>Return Summary</h6>
                  <p class="mb-0"><strong>Total Items Returned:</strong> {{ $returnItems->count() }} | <strong>Total Quantity Returned:</strong> {{ number_format($returnItems->sum('quantity')) }}</p>
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

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Return Details</h4>
            <div class="card-header-action">
              <a href="{{ route('delivery-return') }}" class="btn btn-primary">Back to Returns</a>
              <a href="{{ route('delivery-return.edit', $return->delivery_return_id) }}" class="btn btn-warning">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <!-- Return Information -->
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h6>Return Information</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-borderless">
                      <tr>
                        <td><strong>Return No:</strong></td>
                        <td>{{ $return->return_no }}</td>
                      </tr>
                      <tr>
                        <td><strong>Return Date:</strong></td>
                        <td>{{ $return->return_date }}</td>
                      </tr>
                      <tr>
                        <td><strong>Return Reason:</strong></td>
                        <td>{{ $return->return_reason }}</td>
                      </tr>
                      <tr>
                        <td><strong>Description:</strong></td>
                        <td>{{ $return->return_description ?? 'N/A' }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h6>Original Delivery Information</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-borderless">
                      <tr>
                        <td><strong>Order No:</strong></td>
                        <td>{{ $return->order_no }}</td>
                      </tr>
                      <tr>
                        <td><strong>Job No:</strong></td>
                        <td>{{ $return->job_no }}</td>
                      </tr>
                      <tr>
                        <td><strong>Customer:</strong></td>
                        <td>{{ $return->fname }} {{ $return->lname }}</td>
                      </tr>
                      <tr>
                        <td><strong>Stock No:</strong></td>
                        <td>{{ $return->stock_no }}</td>
                      </tr>
                      <tr>
                        <td><strong>Delivery Date:</strong></td>
                        <td>{{ $return->stock_date }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Returned Items -->
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <h6>Returned Items</h6>
                  </div>
                  <div class="card-body">
                    @if($returnItems->count())
                      <div class="table-responsive">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Sr.</th>
                              <th>Item</th>
                              <th>Article No</th>
                              <th>Size</th>
                              <th>Stage</th>
                              <th>Original Qty</th>
                              <th>Returned Qty</th>
                              <th>Unit</th>
                              <th>Reason</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($returnItems as $item)
                            <tr>
                              <td>{{ $loop->index + 1 }}</td>
                              <td>
                                @if($item->product_name)
                                  {{ $item->product_name }}
                                @else
                                  {{ $item->material_name }}
                                @endif
                              </td>
                              <td>{{ $item->article_no ?? 'N/A' }}</td>
                              <td>{{ $item->size_name ?? 'N/A' }}</td>
                              <td>{{ $item->stage_name ?? 'N/A' }}</td>
                              <td>{{ number_format($item->original_quantity) }}</td>
                              <td>{{ number_format($item->quantity) }}</td>
                              <td>{{ $item->product_unit ?? $item->material_unit ?? 'N/A' }}</td>
                              <td>{{ $item->reason ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>

                      <!-- Summary -->
                      <div class="row mt-3">
                        <div class="col-md-12">
                          <div class="alert alert-info">
                            <h6>Return Summary</h6>
                            <p><strong>Total Items Returned:</strong> {{ $returnItems->count() }}</p>
                            <p><strong>Total Quantity Returned:</strong> {{ number_format($returnItems->sum('quantity')) }}</p>
                          </div>
                        </div>
                      </div>
                    @else
                      <div class="alert alert-warning">
                        <h5>No Items Returned</h5>
                        <p>No items have been returned for this delivery return record.</p>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

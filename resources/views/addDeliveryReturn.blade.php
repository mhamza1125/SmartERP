@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Process Delivery Return</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <!-- Delivery Information -->
            <div class="row mb-4">
              <div class="col-md-12">
                <div class="card bg-light">
                  <div class="card-body">
                    <h5>Delivery Information
                      @if(isset($isMultiOrder) && $isMultiOrder)
                        <span class="badge badge-secondary ml-2">Multi-Order</span>
                      @endif
                    </h5>
                    <div class="row">
                      <div class="col-md-6">
                        @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
                          <strong>Primary Order:</strong> {{ $delivery->order_no }}<br>
                          <strong>Primary Job No:</strong> {{ $delivery->job_no }}<br>
                          <strong>All Orders:</strong>
                          @foreach($relatedOrders as $index => $order)
                            @if($index < 3)
                              <span class="badge badge-secondary mr-1">{{ $order->order_no ?? 'N/A' }}</span>
                            @endif
                          @endforeach
                          @if(count($relatedOrders) > 3)
                            <span class="badge badge-light">+{{ count($relatedOrders) - 3 }} more</span>
                          @endif
                          <br>
                        @else
                          <strong>Order No:</strong> {{ $delivery->order_no }}<br>
                          <strong>Job No:</strong> {{ $delivery->job_no }}<br>
                        @endif
                        <strong>Customer:</strong> {{ $delivery->fname }} {{ $delivery->lname }}
                      </div>
                      <div class="col-md-6">
                        <strong>Stock No:</strong> {{ $delivery->stock_no }}<br>
                        <strong>Delivery Date:</strong> {{ $delivery->stock_date }}<br>
                        <strong>Delivery Method:</strong> {{ $delivery->delivery_method }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <form action="{{ route('delivery-return.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <input type="hidden" name="delivery_id" value="{{ $delivery->delivery_id }}">
              
              <!-- Return Details -->
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="return_no">Return No <span class="text-danger">*</span></label>
                    <input type="text" name="return_no" id="return_no" class="form-control" value="{{ $returnNo }}" readonly required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="return_date">Return Date <span class="text-danger">*</span></label>
                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="return_reason">Return Reason <span class="text-danger">*</span></label>
                    <select name="return_reason" id="return_reason" class="form-control" required>
                      <option value="">-- Select Reason --</option>
                      <option value="Defective">Defective Product</option>
                      <option value="Wrong Item">Wrong Item Delivered</option>
                      <option value="Customer Request">Customer Request</option>
                      <option value="Quality Issue">Quality Issue</option>
                      <option value="Damaged">Damaged During Delivery</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Additional details about the return..."></textarea>
                  </div>
                </div>
              </div>

              <!-- Returnable Items -->
              <div class="row">
                <div class="col-md-12">
                  <h5>Items to Return</h5>
                  @if($returnableItems->count())
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Item</th>
                            <th>Article No</th>
                            <th>Size</th>
                            <th>Stage</th>
                            <th>Delivered Qty</th>
                            <th>Already Returned</th>
                            <th>Available to Return</th>
                            <th>Return Qty</th>
                            <th>Reason</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($returnableItems as $item)
                          <tr>
                            <input type="hidden" name="stock_item_id[]" value="{{ $item->stock_item_id }}">
                            <td>
                              @if($item->name)
                                {{ $item->name }}
                              @else
                                {{ $item->mname }}
                              @endif
                            </td>
                            <td>{{ $item->article_no ?? 'N/A' }}</td>
                            <td>{{ $item->hname ?? 'N/A' }}</td>
                            <td>{{ $item->sname ?? 'N/A' }}</td>
                            <td>{{ number_format($item->quantity) }} {{ $item->puname ?? $item->uname }}</td>
                            <td>{{ number_format($item->returned_qty) }} {{ $item->puname ?? $item->uname }}</td>
                            <td>{{ number_format($item->returnable_qty) }} {{ $item->puname ?? $item->uname }}</td>
                            <td>
                              <input type="number" name="return_quantity[]" class="form-control" min="0" max="{{ $item->returnable_qty }}" step="0.01" placeholder="0">
                            </td>
                            <td>
                              <input type="text" name="reason[]" class="form-control" placeholder="Specific reason...">
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  @else
                    <div class="alert alert-info">
                      <h5>No Items Available for Return</h5>
                      <p>All items from this delivery have already been returned or there are no items eligible for return.</p>
                    </div>
                  @endif
                </div>
              </div>

              @if($returnableItems->count())
              <div class="row">
                <div class="col-md-12">
                  <button type="submit" class="btn btn-success">Process Return</button>
                  <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                </div>
              </div>
              @endif
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Info</h4>
            <div class="card-header-action">
              <div class="dropdown">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-print"></i> Print
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('order.print', $order['order_id']) }}" target="_blank">
                    <i class="fas fa-file-alt"></i> Print Order
                  </a>
                  <a class="dropdown-item" href="{{ route('order.production', $order['order_id']) }}" target="_blank">
                    <i class="fas fa-industry"></i> Production Order
                  </a>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#proformaModal">
                    <i class="fas fa-file-invoice"></i> Proforma Invoice
                  </a>
                </div>
              </div>
              <div class="btn-group">
                @if(isset($ptcs) && $ptcs->count() > 0)
                  <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#ptcModal">
                    <i class="fas fa-tasks"></i> PTCs ({{ $ptcs->count() }})
                  </button>
                @endif
                <a href="{{ route('order.estimate', $order['order_id']) }}" class="btn btn-primary">Estimate</a>
                <a href="{{ route('order.status', $order['order_id']) }}" class="btn btn-primary">Order Status</a>
                <a href="{{ route('delivery.add', $order['order_id']) }}" class="btn btn-primary">Deliver</a>
                <a href="{{ route('order') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('order.edit', $order['order_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer No:</b> {{$order['customer_no']}}</td></tr>
                    <tr><td><b>Customer Name:</b> {{$order['fname']}} {{$order['lname']}}</td></tr>
                    @if($order['payment_terms'])
                    <tr><td><b>Payment Terms:</b> {{$order['payment_terms']}}</td></tr>
                    @endif
                    <tr><td><b>Order Status:</b>
                      @if($order['order_status'] == 1) <span class="badge badge-secondary">Draft</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Confirmed</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-info">Dispatched</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-primary">Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-danger">Cancelled</span>
                      @else @endif
                    </td></tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    {{-- <tr><td><b>Voucher No:</b> SLE-{{ date('Y') }}-{{ str_pad($order['order_id'], 4, '0', STR_PAD_LEFT) }}</td></tr> --}}
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                    @if($order['due_date'])
                    <tr><td><b>Delivery Date:</b> {{$order['due_date']}}</td></tr>
                    @endif
                    {{-- @if($order['expected_delivery_date'])
                    <tr><td><b>Expected Delivery:</b> {{$order['expected_delivery_date']}}</td></tr>
                    @endif --}}
                    
                  </tbody>
                </table>
              </div>
            </div>
            @if($order['so_origin'])
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label><strong>Statement of Origin:</strong></label>
                  <div class="border p-3 bg-light">
                    {!! nl2br(e($order['so_origin'])) !!}
                  </div>
                </div>
              </div>
            </div>
            @endif

            <!-- Tab Navigation -->
            {{-- <ul class="nav nav-tabs" id="orderTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="order-details-tab" data-toggle="tab" href="#order-details" role="tab" aria-controls="order-details" aria-selected="true">Order Details</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="packing-list-tab" data-toggle="tab" href="#packing-list" role="tab" aria-controls="packing-list" aria-selected="false">Packing List</a>
              </li>
            </ul> --}}

            <!-- Tab Content -->
            <div class="tab-content" id="orderTabContent">
              <!-- Order Details Tab -->
              <div class="tab-pane fade show active" id="order-details" role="tabpanel" aria-labelledby="order-details-tab">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Product Stage</th>
                          <th>Size</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          {{-- <th>Box Quantity</th> --}}
                          <th>Price (Customer Currency)</th>
                          {{-- <th>Price (PKR)</th> --}}
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($orderItem->count())
                          @php $product_id = 0; @endphp
                          @foreach($orderItem as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              @if($item->product_id == $product_id)
                                <td colspan="2"></td>
                              @else
                                <td>{{$item->article_no}}</td>
                                <td>{{$item->pname}}</td>
                              @endif
                              <td>{{$item->sname}}</td>
                              <td>{{$item->name}}</td>
                              <td>{{$item->uname}}</td>
                              <td>{{$item->quantity}}</td>
                              {{-- <td>{{ $item->box_quantity ? number_format($item->box_quantity) . ' boxes' : 'N/A' }}</td> --}}
                              <td>{{number_format($item->price, 2)}} {{$item->cname}}</td>
                              <td>{{number_format($item->quantity * $item->price, 2)}}</td>
                            </tr>
                          @php $product_id = $item->product_id; @endphp
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        @php
                          // Calculate total in customer currency (price is now customer currency)
                          $totalCustomerCurrency = $orderItem->sum(function($item) {
                            return $item->quantity * $item->price;
                          });
                          $firstItem = $orderItem->first();
                          $currencyName = $firstItem->cname ?? 'PKR';
                        @endphp
                        <tr>
                          <th colspan="7"></th>
                          <th>Grand Total ({{ $currencyName }}):</th>
                          <th>{{ number_format($totalCustomerCurrency, 2) }}</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Packing List Tab -->
              <div class="tab-pane fade" id="packing-list" role="tabpanel" aria-labelledby="packing-list-tab">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Product</th>
                          <th>Pieces/Boxes</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(isset($packingList['items']) && count($packingList['items']) > 0)
                          @php $index = 1; @endphp
                          @foreach($packingList['items'] as $product => $data)
                            <tr>
                              <td>{{ $index++ }}</td>
                              <td>{{ $product }}</td>
                              <td>{{ number_format($data['quantity']) }} pcs / {{ number_format($data['boxes'], 2) }} boxes</td>
                            </tr>
                          @endforeach
                        @else
                          <tr>
                            <td colspan="3" class="text-center">No packing data available</td>
                          </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th colspan="2">Total:</th>
                          <th>{{ isset($packingList['totalQuantity']) ? number_format($packingList['totalQuantity']) : 0 }} pcs / {{ isset($packingList['totalBoxes']) ? number_format($packingList['totalBoxes']) : 0 }} boxes</th>
                        </tr>
                      </tfoot>
                    </table>
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

<script>
function printProformaInvoice() {
    var bankId = document.getElementById('proformaBankSelect').value;
    var hsCode = document.getElementById('proformaHsCode').value;
    var sellingType = document.getElementById('proformaSellingType').value;
    var uom = document.getElementById('proformaUOM').value;
    var url = "{{ route('order.proforma', $order['order_id']) }}";

    var params = new URLSearchParams();
    if (bankId) params.append('bank_id', bankId);
    if (hsCode) params.append('hs_code', hsCode);
    if (sellingType) params.append('selling_type', sellingType);
    if (uom) params.append('uom', uom);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    window.open(url, '_blank');
    $('#proformaModal').modal('hide');
}
</script>

<!-- Proforma Invoice Modal -->
<div class="modal fade" id="proformaModal" tabindex="-1" role="dialog" aria-labelledby="proformaModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="proformaModalLabel">Proforma Invoice</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="proformaHsCode"><strong>HS Code (Optional)</strong></label>
          <input type="text" class="form-control" id="proformaHsCode" placeholder="Enter HS Code">
        </div>
        <div class="form-group">
          <label for="proformaSellingType"><strong>Selling Type (Optional)</strong></label>
          <input type="text" class="form-control" id="proformaSellingType" placeholder="Ex Works, FOB, CIF, etc">
        </div>
        <div class="form-group">
          <label for="proformaUOM"><strong>UOM (Optional)</strong></label>
          <input type="text" class="form-control" id="proformaUOM" placeholder="Pair, Dozen, etc">
        </div>
        <div class="form-group">
          <label for="proformaBankSelect"><strong>Select Bank Account (Optional)</strong></label>
          <select class="form-control" id="proformaBankSelect">
            <option value="">-- No Bank Details --</option>
            @if(isset($banks) && $banks->count() > 0)
              @foreach($banks as $bank)
                <option value="{{ $bank->bank_id }}"
                        data-title="{{ $bank->account_title }}"
                        data-account="{{ $bank->account }}"
                        data-iban="{{ $bank->iban ?? '' }}"
                        data-address="{{ $bank->address ?? '' }}"
                        data-branch="{{ $bank->branch_code ?? '' }}"
                        data-swift="{{ $bank->swift_code ?? '' }}">
                  {{ $bank->account_title }} - {{ $bank->account }}
                </option>
              @endforeach
            @endif
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="printProformaInvoice()">
          <i class="fas fa-print"></i> Print Proforma Invoice
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Production Tracking Cards (PTCs) Modal -->
@if(isset($ptcs) && $ptcs->count() > 0)
<div class="modal fade" id="ptcModal" tabindex="-1" role="dialog" aria-labelledby="ptcModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="ptcModalLabel">
          <i class="fas fa-tasks"></i> Process Travel Cards (PTCs)
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        @if($ptcs->count() > 0)
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-light">
                <tr>
                  <th style="width: 15%">PTC No</th>
                  <th style="width: 20%">Quantity</th>
                  <th style="width: 25%">Status</th>
                  <th style="width: 20%">Date</th>
                  <th style="width: 20%">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($ptcs as $ptc)
                  @php
                    // Extract quantity from description field
                    // Description format: "Qty: XXX" or similar
                    $quantity = 'N/A';
                    if ($ptc->description) {
                      // Try to extract quantity from description
                      if (preg_match('/Qty:\s*(\d+)/i', $ptc->description, $matches)) {
                        $quantity = $matches[1];
                      } elseif (preg_match('/(\d+)\s*units?/i', $ptc->description, $matches)) {
                        $quantity = $matches[1];
                      }
                    }

                    // Determine status badge based on order_status or stock_status
                    $statusBadge = 'badge-secondary';
                    $statusText = 'Unknown';

                    // Use order_status if available, otherwise use stock_status
                    $status = $ptc->stock_status ?? $ptc->order_status;

                    if ($status == 1) {
                      $statusBadge = 'badge-secondary';
                      $statusText = 'Draft';
                    } elseif ($status == 2) {
                      $statusBadge = 'badge-success';
                      $statusText = 'Confirmed';
                    } elseif ($status == 3) {
                      $statusBadge = 'badge-info';
                      $statusText = 'Dispatched';
                    } elseif ($status == 4) {
                      $statusBadge = 'badge-primary';
                      $statusText = 'Delivered';
                    } elseif ($status == 5) {
                      $statusBadge = 'badge-danger';
                      $statusText = 'Cancelled';
                    } elseif ($status == 6) {
                      $statusBadge = 'badge-warning';
                      $statusText = 'In Progress';
                    } elseif ($status == 7) {
                      $statusBadge = 'badge-success';
                      $statusText = 'Completed';
                    }
                  @endphp
                  <tr>
                    <td>
                      <strong>PTC-{{ $ptc->stock_no }}</strong>
                    </td>
                    <td>
                      {{ $quantity }}
                    </td>
                    <td>
                      <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                    </td>
                    <td>
                      {{ $ptc->stock_date ?? 'N/A' }}
                    </td>
                    <td>
                      <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-sm btn-info" title="View PTC Details">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No Production Tracking Cards found for this order.
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endif

@endsection
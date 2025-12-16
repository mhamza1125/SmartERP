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
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Voucher No:</b> TXN-{{ date('Y') }}-{{ str_pad($order['order_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                    @if($order['due_date'])
                    <tr><td><b>Due Date:</b> {{$order['due_date']}}</td></tr>
                    @endif
                    @if($order['payment_terms'])
                    <tr><td><b>Payment Terms:</b> {{$order['payment_terms']}}</td></tr>
                    @endif
                    {{-- @if($order['expected_delivery_date'])
                    <tr><td><b>Expected Delivery:</b> {{$order['expected_delivery_date']}}</td></tr>
                    @endif --}}
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
            <ul class="nav nav-tabs" id="orderTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="order-details-tab" data-toggle="tab" href="#order-details" role="tab" aria-controls="order-details" aria-selected="true">Order Details</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="packing-list-tab" data-toggle="tab" href="#packing-list" role="tab" aria-controls="packing-list" aria-selected="false">Packing List</a>
              </li>
            </ul>

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
                          <th>Box Quantity</th>
                          <th>Price (Currency)</th>
                          <th>Exchange (Pkr)</th>
                          <th>Price (Pkr)</th>
                          <th>Total (Pkr)</th>
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
                              <td>{{$item->hname}}</td>
                              <td>{{$item->uname}}</td>
                              <td>{{$item->quantity}}</td>
                              <td>{{ $item->box_quantity ? number_format($item->box_quantity) . ' boxes' : 'N/A' }}</td>
                              <td>{{$item->price2}} {{$item->cname}}</td>
                              <td>{{$item->exchange}}</td>
                              <td>{{$item->price}}</td>
                              <td>{{$item->quantity * $item->price}}</td>
                            </tr>
                          @php $product_id = $item->product_id; @endphp
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        @php
                          $total = $orderItem->sum(function($item) {
                            return $item->quantity * $item->price;
                          });
                          // Calculate total in original currency
                          $firstItem = $orderItem->first();
                          $currencyName = $firstItem->cname ?? 'PKR';
                          $exchangeRate = $firstItem->exchange ?? 1;
                          // If exchange rate is not 1 and currency is not PKR, calculate original currency total
                          $hasExchange = $exchangeRate > 0 && $exchangeRate != 1 && strtoupper($currencyName) != 'PKR';
                          if ($hasExchange) {
                            $totalOriginal = $orderItem->sum(function($item) {
                              return $item->quantity * ($item->price2 ?? 0);
                            });
                          }
                        @endphp
                        <tr>
                          <th colspan="10"></th>
                          <th>Grand Total:</th>
                          <th>
                            @if($hasExchange)
                              {{ number_format($totalOriginal, 2) }} {{ $currencyName }} (PKR {{ number_format($total) }})
                            @else
                              PKR {{ number_format($total) }}
                            @endif
                          </th>
                        </tr>
                        <tr>
                          <th colspan="10"></th>
                          <th>Amount in Words:</th>
                          <th>{{ numberToWordsWithCurrency($total) }}</th>
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
    var url = "{{ route('order.proforma', $order['order_id']) }}";

    if (bankId) {
        url += '?bank_id=' + bankId;
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

@endsection
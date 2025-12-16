@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Requirements for Order</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
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
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                    <tr><td><b>Order Status:</b> 
                      @if($order['order_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Processing</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-warning">On Hold</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-success">Partially Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-success">Delivered</span>
                      @elseif($order['order_status'] == 6) <span class="badge badge-success">Completed</span>
                      @elseif($order['order_status'] == 7) <span class="badge badge-danger">Canceled</span>
                      @elseif($order['order_status'] == 8) <span class="badge badge-danger">Returned</span>
                      @elseif($order['delivery_status'] == 9) <span class="badge badge-warning">Disputed</span>
                      @else @endif
                    </td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="save-stage" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Current Vendor</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Purchased Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($estimate->count())
                        @foreach($estimate as $item)
                            @php
                                $stockItem = $stock[$item->material_id] ?? (object)[
                                  'total_qty' => 0, 'stockIn' => 0, 'total_received' => '0',
                                  'total_returned' => 0, 'stockOut' => 0
                                ];
                                $available = $stockItem->total_received + $stockItem->stockIn - $stockItem->total_returned - $stockItem->stockOut;
                                $purchaseItem = $purchase[$item->material_id] ?? null;
                                $purchased = $purchaseItem ? $purchaseItem['total_qty'] : 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $item->material_no }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->vendor_no }} - {{ $item->fname }}</td>
                                <td>{{ number_format($item->total_qty, 2) }}</td>
                                <td>{{ number_format($available) }}</td>
                                <td>{{ number_format($purchased) }}</td>
                                <td>{{ number_format(max($item->total_qty - $available, 0)) }}</td>
                                <td>{{ $item->hname }}</td>
                            </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Current Vendor</th>
                        <th>Total Require Qty</th>
                        <th>Available Qty</th>
                        <th>Purchased Qty</th>
                        <th>Required Qty</th>
                        <th>Unit</th>
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
</section>
@endsection
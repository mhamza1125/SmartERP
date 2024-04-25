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
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('purchase.add')}}" class="btn btn-primary" target="_blank">Purchase</a>
              </div>
            </div>
          </div>
          <div class="card-body row">
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
                </tbody>
              </table>
            </div>
            <div class="col md-12">
              <table class="table table-sm">
                <tbody>
                  <tr>
                    {{-- <td><b>Vendor:</b> {{$vendor['vendor_no']}} - {{$vendor['fname']}}</td>
                    <td><b>Vendor Type:</b> {{$vendor['vtname']}}</td>
                    <td><b>Contact:</b> {{$vendor['phone1']}}</td>
                    <td><b>Payable Amount:</b> {{number_format($balance)}}</td> --}}
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-12">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material Name</th>
                    <th>Current Vendor</th>
                    <th>Total Qty</th>
                    <th>Available Qty</th>
                    <th>Required Qty</th>
                    <th>Unit</th>
                  </tr>
                </thead>
                <tbody>
                  @if($estimate->count())
                    @foreach($estimate as $item)
                        @php
                            $stockItem = $stock[$item->material_id];
                            $available = $stockItem->total_received + $stockItem->stockIn - $stockItem->total_returned - $stockItem->stockOut;
                        @endphp
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ $item->material_no }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->vendor_no }} - {{ $item->fname }}</td>
                            <td>{{ number_format($item->total_qty) }}</td>
                            <td>{{ number_format($available) }}</td>
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
                    <th>Total Qty</th>
                    <th>Available Qty</th>
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
</section>
@endsection
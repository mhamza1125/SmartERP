@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <button type="button" class="btn btn-info" onclick="printPage('Delivery Information')">
                  <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('delivery') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('delivery.edit', $delivery['delivery_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer No:</b> {{$delivery['customer_no']}}</td></tr>
                    {{-- <tr><td><b>Customer Name:</b> {{$delivery['fname']}} {{$delivery['lname']}}</td></tr> --}}
                    <tr><td><b>Order No</b> {{$delivery['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$delivery['job_no']}}</td></tr>
                    <tr><td><b>Order Date:</b> {{$delivery['order_date']}}</td></tr>
                    @if($delivery['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td>@php echo $delivery['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Delivery No:</b> {{$delivery['customer_no']}}</td></tr>
                    <tr><td><b>Shipping From:</b> {{$delivery['fshipping']}}</td></tr>
                    <tr><td><b>Port No:</b> {{$delivery['fport_no']}}</td></tr>
                    <tr><td><b>Shipping To:</b> {{$delivery['tshipping']}}</td></tr>
                    <tr><td><b>Port No:</b> {{$delivery['tport_no']}}</td></tr>
                    <tr><td><b>Delivery Method: </b> 
                      @if($delivery['delivery_method'] == 1) Sea Freight 
                      @elseif($delivery['delivery_method'] == 2) Air Freight 
                      @elseif($delivery['delivery_method'] == 3) Road Transport 
                      @else Unknown @endif</td></tr>
                    <tr><td><b>Delivery Status:</b> 
                      @if($delivery['delivery_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($delivery['delivery_status'] == 2) <span class="badge badge-success">Delivered</span>
                      @elseif($delivery['delivery_status'] == 3) <span class="badge badge-danger">Returned</span>
                      @elseif($delivery['delivery_status'] == 4) <span class="badge badge-danger">Disputed</span>
                      @else @endif
                    </td></tr>
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
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryItem->count())
                      @php $product_id = 0; $index = 1; @endphp
                      @foreach($deliveryItem as $item)
                        @unless($item->product_type_id == 0)
                          <tr>
                            <td>{{$index++}}</td>
                            @if($item->product_id == $product_id)
                              <td colspan="2"></td>
                            @else
                              <td>{{$item->article_no}}</td>
                              <td>{{$item->name}}</td>
                            @endif
                            <td>{{$item->sname}}</td>
                            <td>{{$item->hname}}</td>
                            <td>{{$item->puname}}</td>
                            <td>{{$item->quantity}}</td>
                          </tr>
                        @endunless
                      @php $product_id = $item->product_id; @endphp
                      @endforeach
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
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <h5>Delivery to Container</h5>
            @if($deliveryBox->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Vehicle No</th>
                      <th>Row 1</th>
                      <th>Row 2</th>
                      <th>Row 3</th>
                      <th>Row 4</th>
                      <th>Row 5</th>
                      <th>Row 6</th>
                      <th>Row 7</th>
                      <th>Row 8</th>
                      <th>Total Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryBox->count())
                      @php $total = 0; @endphp
                      @foreach($deliveryBox as $item)
                        @php
                           $rowQtys = explode('|', $item->rowQty);
                           $total += $item->totalQty
                        @endphp
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->vehicle_no}}</td>
                          @foreach($rowQtys as $qty)
                            <td>{{$qty}}</td>
                          @endforeach
                          <td>{{$item->totalQty}}</td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="7"></th>
                      <th colspan="2">Grand Total:</th>
                      <th colspan="2">{{$total}} Boxes</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery to Container Record </blockquote>
            @endif

            <h5>Delivery Container / Vehicle</h5>
            @if($deliveryItem->where('product_type_id', 0)->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Vehicle Type</th>
                      <th>Vehicle Name</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryItem->count())
                      @php $index = 1; @endphp
                      @foreach($deliveryItem as $item)
                        @unless($item->product_type_id != 0)
                          <tr>
                            <td>{{$index++}}</td>
                            <td>{{$item->material_no}}</td>
                            <td>{{$item->mname}}</td>
                            <td>{{$item->quantity}}</td>
                          </tr>
                        @endunless
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery Conatiner / Vehicle </blockquote>
            @endif

            <h5>Delivery Expense</h5>
            @if($transaction->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Head</th>
                      <th>Paid By</th>
                      <th>Amount</th>
                      <th>Detail</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($transaction->count())
                      @foreach($transaction as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->name}}</td>
                          <td>@if(isset($item->bname))
                                {{$item->bname}} - {{$item->account_title}} - {{$item->account}}
                              @else
                                Cash Payment
                              @endif</td>
                          <td>{{number_format($item->debit)}}</td>
                          <td>{{$item->description}}</td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery Expense </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
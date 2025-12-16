@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Table</h4>
            <div class="card-header-action">
              <a href="{{ route('order.add') }}" class="btn btn-primary">Add Order</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Order No</th>
                    <th>Job No</th>
                    <th>Customer</th>
                    {{-- <th>Expected Delivery</th> --}}
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($order->count())
                    @foreach($order as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->order_date}}</td>
                      <td>{{$item->order_no}}</td>
                      <td>{{$item->job_no}}</td>
                      <td>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</td>
                      {{-- <td>
                        @if($item->expected_delivery_date)
                          <span class="badge badge-info">{{$item->expected_delivery_date}}</span>
                        @else
                          <span class="text-muted">Not set</span>
                        @endif
                      </td> --}}
                      <td>
                        <div class="btn-group">
                            <button class="btn <?php
                                if($item->order_status == 1){ echo 'btn-secondary'; $status = 'Draft'; }
                                elseif($item->order_status == 2){ echo 'btn-success'; $status = 'Confirmed'; }
                                elseif($item->order_status == 3){ echo 'btn-info'; $status = 'Dispatched'; }
                                elseif($item->order_status == 4){ echo 'btn-primary'; $status = 'Delivered'; }
                                elseif($item->order_status == 5){ echo 'btn-danger'; $status = 'Cancelled'; }
                                else{ echo 'btn-danger'; $status = 'Unknown'; }
                               ?> btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" {{ in_array($item->order_status, [3, 4, 5]) ? 'disabled' : '' }}>
                              {{$status}}
                            </button>
                            @if(!in_array($item->order_status, [3, 4, 5]))
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="{{ route('order.updateStatus', ['id' => $item->order_id, 'status' => '1']) }}">Draft</a>
                              <a class="dropdown-item" href="{{ route('order.updateStatus', ['id' => $item->order_id, 'status' => '2']) }}">Confirmed</a>
                              <a class="dropdown-item" href="{{ route('order.updateStatus', ['id' => $item->order_id, 'status' => '3']) }}">Dispatched</a>
                              <a class="dropdown-item" href="{{ route('order.updateStatus', ['id' => $item->order_id, 'status' => '4']) }}">Delivered</a>
                              <a class="dropdown-item" href="{{ route('order.updateStatus', ['id' => $item->order_id, 'status' => '5']) }}">Cancelled</a>
                            </div>
                            @endif
                          </div>
                      </td>
                      <td>
                        <a href="{{ route('order.show', $item->order_id) }}" class="btn btn-info btn-sm">View</a>
                        @if(!in_array($item->order_status, [3, 4, 5]))
                          <a href="{{ route('order.edit', $item->order_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        @else
                          <button class="btn btn-secondary btn-sm" disabled title="Cannot edit Dispatched, Delivered, or Cancelled orders">Edit</button>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Order No</th>
                    <th>Job No</th>
                    <th>Customer</th>
                    {{-- <th>Expected Delivery</th> --}}
                    <th>Status</th>
                    <th>Action</th>
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
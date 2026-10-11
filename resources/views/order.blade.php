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
                      <td>{{\Carbon\Carbon::parse($item->order_date)->format('d-m-Y')}}</td>
                      <td>{{$item->order_no}}</td>
                      <td>{{$item->job_no}}</td>
                      <td>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</td>
                      {{-- <td>
                        @if($item->expected_delivery_date)
                          <span class="badge badge-info">{{ formatDate($item->expected_delivery_date) }}</span>
                        @else
                          <span class="text-muted">Not set</span>
                        @endif
                      </td> --}}
                      <td>
                        @php
                          $statusLabel = \App\Models\Order::STATUS_LABELS[$item->order_status] ?? 'Unknown';
                          $statusClass = [1 => 'btn-secondary', 2 => 'btn-success', 3 => 'btn-info', 4 => 'btn-primary', 5 => 'btn-danger'][$item->order_status] ?? 'btn-danger';
                        @endphp
                        <div class="btn-group">
                            <button class="btn {{ $statusClass }} btn-sm dropdown-toggle" type="button" data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" title="Change status">
                              {{ $statusLabel }}
                            </button>
                            {{-- Status can always be changed; Dispatched / Delivered / Cancelled lock order editing --}}
                            <form class="dropdown-menu" method="POST" action="{{ route('order.updateStatus', $item->order_id) }}"
                              onsubmit="return confirm('Change the status of order {{ $item->job_no }}?');">
                              @csrf
                              @foreach(\App\Models\Order::STATUS_LABELS as $value => $label)
                                <button type="submit" name="order_status" value="{{ $value }}"
                                  class="dropdown-item {{ $item->order_status == $value ? 'active' : '' }}"
                                  {{ $item->order_status == $value ? 'disabled' : '' }}>{{ $label }}</button>
                              @endforeach
                            </form>
                          </div>
                      </td>
                      <td>
                        <a href="{{ route('order.show', $item->order_id) }}" class="btn btn-info btn-sm">View</a>
                        @if(\App\Models\Order::isEditableStatus($item->order_status))
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
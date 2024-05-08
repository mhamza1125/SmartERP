@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Table</h4>
            <div class="card-header-action">
              <a href="{{ route('delivery') }}" class="btn btn-primary">Add Delivery</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Delivery No</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($delivery->count())
                    @foreach($delivery as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->stock_no}}</td>
                      <td>{{$item->job_no}}</td>
                      <td>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</td>
                      <td>
                        <div class="btn-group">
                            <button class="btn <?php 
                                if($item->delivery_status == 1){ echo 'btn-warning'; $status = 'Pending'; }
                                elseif($item->delivery_status == 2){ echo 'btn-info'; $status = 'Processing'; }
                                elseif($item->delivery_status == 3){ echo 'btn-secondary'; $status = 'On Hold'; }
                                elseif($item->delivery_status == 4){ echo 'btn-primary'; $status = 'Partially Delivered'; }
                                elseif($item->delivery_status == 5){ echo 'btn-success'; $status = 'Delivered'; }
                                elseif($item->delivery_status == 6){ echo 'btn-dark'; $status = 'Completed'; }
                                elseif($item->delivery_status == 7){ echo 'btn-danger'; $status = 'Canceled'; }
                                elseif($item->delivery_status == 8){ echo 'btn-danger'; $status = 'Returned'; }
                                elseif($item->delivery_status == 9){ echo 'btn-warning'; $status = 'Disputed'; }
                                else{ echo 'btn-danger'; $status = 'Unknown'; }
                               ?> btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              {{$status}}
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '1']) }}">Pending</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '2']) }}">Processing</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '3']) }}">On Hold</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '4']) }}">Partially Deilvered</a>
                            </div>
                          </div>
                      </td>
                      <td>{{$item->stock_date}}</td>                      
                      <td>
                        <a href="{{ route('delivery.show', $item->delivery_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('delivery.edit', $item->delivery_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Order No</th>
                    <th>Job No</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Date</th>
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Purchase Table</h4>
            <div class="card-header-action">
              <a href="{{ route('purchase.add') }}" class="btn btn-primary">Add Purchase</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Purchase No</th>
                    <th>Job No</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($purchase->count())
                    @foreach($purchase as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->purchase_no}}</td>
                      <td>{{($item->job_no)? $item->job_no:'Default Purchase'}}</td>
                      <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                      <td>@if($item->has_received) 
                        <span class="badge badge-success">Received</span> @else 
                        <span class="badge badge-danger">To Receive</span> @endif
                      </td>
                      <td>{{$item->purchase_date}}</td>                      
                      <td>
                        <a href="{{ route('purchase.show', $item->purchase_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('purchase.edit', $item->purchase_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('receive.add', $item->purchase_id) }}" class="btn btn-success btn-sm">Receive</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Purchase No</th>
                    <th>Job No</th>
                    <th>Vendor</th>
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
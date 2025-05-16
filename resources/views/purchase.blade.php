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
              <div class="btn-group">
                <a href="{{ route('purchase.add') }}" class="btn btn-primary">Add Material Purchase</a>
                <a href="{{ route('productPurchase.add') }}" class="btn btn-primary">Add Product Purchase</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Purchase No</th>
                    <th>Purchase Type</th>
                    <th>Job No</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($purchase->count())
                    @foreach($purchase as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->purchase_date}}</td>                      
                      <td>{{$item->purchase_no}}</td>
                      <td><span class="badge {{ $item->purchase_type == 'material' ? 'badge-secondary' : 'badge-dark' }}">{{ucfirst($item->purchase_type)}}</span></td>
                      <td>{{($item->job_no)? $item->job_no:'Default Purchase'}}</td>
                      <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                      <td>@if($item->has_received) 
                        <span class="badge badge-success">Received</span> @else 
                        <span class="badge badge-danger">To Receive</span> @endif
                      </td>
                      <td>
                        <a href="{{ route('purchase.show', $item->purchase_id) }}" class="btn btn-info btn-sm">View</a>
                        @if(!$item->has_received)
                          <a href="{{ route($item->purchase_type == 'material' ? 'purchase.edit' : 'productPurchase.edit', $item->purchase_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        @endif
                        <a href="{{ route('receive.add', $item->purchase_id) }}" class="btn btn-success btn-sm">Receive</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Purchase No</th>
                    <th>Purchase Type</th>
                    <th>Job No</th>
                    <th>Vendor</th>
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
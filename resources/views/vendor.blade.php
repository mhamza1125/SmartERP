@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Vendor Table</h4>
            <div class="card-header-action">
              <a href="{{ route('vendor.add') }}" class="btn btn-primary">Add Vendor</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Vendor No</th>
                    <th>Name</th>
                    <th>Vendor Type</th>
                    {{-- <th>Vendor As Worker</th> --}}
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($vendor->count())
                    @foreach($vendor as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->vendor_no}}</td>
                      <td>{{$item->fname}}</td>
                      <td>{{$item->vtname}}</td>
                      {{-- <td><b>@if($item->vendor_type) 
                        <span class="badge badge-success">Active</span> @else 
                        <span class="badge badge-danger">Inactive</span> @endif</td>  --}}
                      <td>
                        <a href="{{ route('vendor.show', $item->vendor_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('vendor.edit', $item->vendor_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('vendor.detail', $item->vendor_id) }}" class="btn btn-success btn-sm">Ledger</a>
                      </td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Vendor No</th>
                      <th>Name</th>
                      <th>Vendor Type</th>
                      {{-- <th>Vendor As Worker</th> --}}
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
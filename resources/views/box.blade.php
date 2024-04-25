@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Boxes Table</h4>
            <div class="card-header-action">
              <a href="{{ route('box.add') }}" class="btn btn-primary">Add Box</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Box No</th>
                    <th>Box Name</th>
                    <th>Material</th>
                    <th>Current Vendor</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($box->count())
                    @foreach($box as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->box_no}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->hname}}</td>
                      <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                      <td>
                        @if($item->box_status)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-danger">Inactive</span>
                        @endif
                      </td>                 
                      <td>
                        <a href="{{ route('box.show', $item->box_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('box.edit', $item->box_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Box No</th>
                    <th>Box Name</th>
                    <th>Material</th>
                    <th>Current Vendor</th>
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
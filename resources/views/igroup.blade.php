@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Groups Table</h4>
            <div class="card-header-action">
              <a href="{{ route('igroup.add') }}" class="btn btn-primary">Add Group</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Group No</th>
                    <th>Status</th>
                    {{-- <th>Order No</th> --}}
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($igroup->count())
                    @foreach($igroup as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->igroup_date}}</td>
                      <td>{{$item->igroup_no}}</td>
                      <td>
                        @if($item->igroup_status)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-danger">Inactive</span>
                        @endif
                      </td>  
                      {{-- <td>{{$item->job_no}}</td> --}}
                      <td>
                        <a href="{{ route('igroup.show', $item->igroup_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('igroup.edit', $item->igroup_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Group No</th>
                    <th>Status</th>
                    {{-- <th>Order No</th> --}}
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
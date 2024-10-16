@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Table</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('stock.add') }}" class="btn btn-primary">Add Issuance</a>
                <a href="{{ route('stock.gadd') }}" class="btn btn-primary">Add Group Issuance</a>
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
                    <th>Issue No</th>
                    <th>Job No</th>
                    <th>Issued For</th>
                    <th>Articles</th>
                    <th>Employee / Vendor</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($issue->count())
                    @foreach($issue as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->stock_date}}</td>                      
                      <td>{{$item->stock_no}}</td>
                      <td>{{($item->job_no)? $item->job_no:'Default issue'}}</td>
                      <td>{{$item->sname}}</td>
                      <td>{{$item->articles}}</td>
                      <td>{{ $item->table_name === 'employee' ? $item->employee_no . ' - ' . $item->name : $item->vendor_no . ' - ' . $item->fname }}</td>
                      <td>
                        @if($item->stock_status == 0)
                            <span class="badge badge-danger">Not Received</span>
                        @elseif($item->stock_status == 1)
                            <span class="badge badge-success">Completely Received</span>
                        @else
                            <span class="badge badge-warning">Partially Received</span>
                        @endif
                      </td>                  
                      <td>
                        <a href="{{ route('stock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                        @if(!$item->stock_status) <a href="{{ route('stock.edit', $item->stock_id) }}" class="btn btn-primary btn-sm">Edit</a> @endif
                        <a href="{{ route('rstock.add', $item->stock_id) }}" class="btn btn-success btn-sm">Receive</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Issue No</th>
                    <th>Job No</th>
                    <th>Issued For</th>
                    <th>Articles</th>
                    <th>Employee / Vendor</th>
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
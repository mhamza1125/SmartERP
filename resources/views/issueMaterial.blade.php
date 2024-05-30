@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Material Material Table</h4>
            <div class="card-header-action">
              <a href="{{ route('mstock.add') }}" class="btn btn-primary">Add Issue</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Employee</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($issue->count())
                    @foreach($issue as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->stock_date}}</td>                      
                      <td>{{$item->machine_no}}</td>
                      <td>{{$item->hname}}</td>
                      <td>{{$item->employee_no}} - {{$item->name}}</td>
                      <td>
                        <a href="{{ route('mstock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('mstock.edit', $item->stock_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Employee</th>
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
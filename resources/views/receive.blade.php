@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Purchase Table</h4>
            <div class="card-header-action">
              {{-- <a href="{{ route('purchase.add') }}" class="btn btn-primary">Add Purchase</a> --}}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($receive->count())
                    @foreach($receive as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->receive_no}}</td>
                      <td>{{$item->purchase_no}}</td>
                      <td>{{$item->fname}}</td>
                      <td>{{$item->receive_date}}</td>                      
                      <td>
                        <a href="{{ route('receive.show', $item->receive_id) }}" class="btn btn-info btn-sm">View</a>
                        {{-- <a href="{{ route('receive.edit', $item->receive_id) }}" class="btn btn-primary btn-sm">Edit</a> --}}
                        <a href="{{ route('return.add', $item->receive_id) }}" class="btn btn-success btn-sm">Return</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
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
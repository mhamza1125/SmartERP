@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Available Stock Table</h4>
            <div class="card-header-action">
              <a href="{{ route('stock.add') }}" class="btn btn-primary">Issue Material</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material Name</th>
                    <th>Quantity</th>
                  </tr>
                </thead>
                <tbody>
                  @if($stock->count())
                    @foreach($stock as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->material_no}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->total_received - $item->total_returned}}</td>                  
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material Name</th>
                    <th>Quantity</th>
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
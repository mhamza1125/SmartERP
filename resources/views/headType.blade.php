@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Heads Table</h4>
            <div class="card-header-action">
              <a href="{{ route('head.add') }}" class="btn btn-primary">Add Head</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Head Type</th>
                    <th>Detail</th>
                  </tr>
                </thead>
                <tbody>
                  @if($headType->count())
                    @foreach($headType as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>                   
                      <td>{{$item->description}}</td>                   
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Head Type</th>
                    <th>Detail</th>
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
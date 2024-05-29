@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Group Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('igroup') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('igroup.edit', $igroup['igroup_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Material / Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="trow">
                      <th></th>
                      <th>Group No: &nbsp {{$igroup['igroup_no']}}</th>
                      <th>Order No: &nbsp {{$igroup['job_no']}}</th>
                      <th></th>
                      <th></th>
                    </tr>
                    @if($igroupItem->count())
                      @foreach($igroupItem as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                          <td>{{($item->name)? $item->name:$item->stage}}</td>
                          <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Material / Stage</th>
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
  </div>
</section>
@endsection
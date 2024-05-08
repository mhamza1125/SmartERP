@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Return Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('return.edit', $return['return_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Return No:</b> {{$return['return_no']}}</td></tr>
                    <tr><td><b>Receive No:</b> {{$return['receive_no']}}</td></tr>
                    <tr><td><b>Vendor Name:</b> {{$return['fname']}}</td></tr>
                    <tr><td><b>Phone:</b> {{$return['phone1']}}</td></tr>
                    <tr><td><b>Address:</b> {{$return['address']}}</td></tr>
                    @if($return['desc'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td>@php echo $return['desc'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>P.O.#:</b> {{$return['purchase_no']}}</td></tr>
                    <tr><td><b>Job.#:</b> {{($return['job_no'])? $return['job_no']:'Default Purchase'}}</td></tr>
                    <tr><td><b>Date:</b> {{$return['purchase_date']}}</td></tr>
                    <tr><td><b>Required Date:</b> {{$return['require_date']}}</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Code</th>
                      <th>Material</th>
                      <th>Units</th>
                      <th>Return Qty</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($returnMaterial->count())
                      @php $loopIndex = 1; @endphp
                      @foreach($returnMaterial as $item)
                        @if($item->quantity)
                        <tr>
                          <td>{{$loopIndex++}}</td>
                          <td>{{$item->material_no}}</td>
                          <td>{{$item->name}}</td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->quantity}}</td>
                          <td>{{$item->remarks}}</td>
                        </tr>
                        @endif
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Code</th>
                      <th>Material</th>
                      <th>Units</th>
                      <th>Return Qty</th>
                      <th>Remarks</th>
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Info</h4>
            <div class="card-header-action">
              
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('receive.edit', $receive['receive_id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('return.add', $receive['receive_id']) }}" class="btn btn-primary">Return</a>
              </div>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-7">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Receive No:</b> {{$receive['receive_no']}}</td></tr>
                  <tr><td><b>Vendor Name:</b> {{$receive['fname']}}</td></tr>
                  <tr><td><b>Phone:</b> {{$receive['phone1']}}</td></tr>
                  <tr><td><b>Address:</b> {{$receive['address']}}</td></tr>
                  @if($receive['desc'])<tr><td><b>Detail:</b></td></tr>
                  <tr><td>@php echo $receive['desc'] @endphp</td></tr>@endif
                </tbody>
              </table>
            </div>
            <div class="col-md-5">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>P.O.#:</b> {{$receive['purchase_no']}}</td></tr>
                  <tr><td><b>Job.#:</b> {{($receive['job_no'])? $receive['job_no']:'Default Purchase'}}</td></tr>
                  <tr><td><b>Date:</b> {{$receive['purchase_date']}}</td></tr>
                  <tr><td><b>Required Date:</b> {{$receive['require_date']}}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-12">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material</th>
                    <th>Units</th>
                    <th>Receive Qty</th>
                  </tr>
                </thead>
                <tbody>
                  @if($receiveMaterial->count())
                    @foreach($receiveMaterial as $item)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{$item->material_no}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->hname}}</td>
                        <td>{{$item->quantity}}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material</th>
                    <th>Units</th>
                    <th>Receive Qty</th>
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
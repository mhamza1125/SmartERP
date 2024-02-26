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
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-7">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Name:</b> {{$purchase['fname']}}</td></tr>
                  <tr><td><b>Phone:</b> {{$purchase['phone1']}}</td></tr>
                  <tr><td><b>Address:</b> {{$purchase['address']}}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-5">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>P.O.#:</b> {{$purchase['purchase_no']}}</td></tr>
                  <tr><td><b>Job.#:</b> {{$purchase['job_no']}}</td></tr>
                  <tr><td><b>Date:</b> {{$purchase['purchase_date']}}</td></tr>
                  <tr><td><b>Required Date:</b> {{$purchase['require_date']}}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-12">
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">All</a>
                </li>
                @if($count > 1)
                  @for($i=1; $i<=$count; $i++)
                    <li class="nav-item">
                      <a class="nav-link" id="tab-{{ $i }}" data-toggle="tab" href="#tab-content-{{ $i }}" role="tab" aria-controls="tab-content-{{ $i }}" aria-selected="false">#R{{ $i }} - {{$totalReceive[$i-1]['receive_date']}}</a>
                    </li>
                  @endfor
                @endif
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">  
                  <table class="table table-sm table-striped">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material</th>
                        <th>Units</th>
                        <th>Order Qty</th>
                        <th>Receive Qty</th>
                        <th>Remaining</th>
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
                            <td>{{$item->rqty}}</td>
                            <td>{{$item->quantity - $item->rqty}}</td>
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
                        <th>Order Qty</th>
                        <th>Receive Qty</th>
                        <th>Remaining</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                @if($count > 1)
                  @for($i=1; $i<=$count; $i++)  
                    <div class="tab-pane fade" id="tab-content-{{ $i }}" role="tabpanel" aria-labelledby="tab-{{ $i }}">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Code</th>
                            <th>Material</th>
                            <th>Units</th>
                            {{-- <th>Order Qty</th> --}}
                            <th>Receive Qty</th>
                            {{-- <th>Remaining</th> --}}
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($receiveMaterialAll as $item)
                            @if($totalReceive[$i-1]['created_at'] == $item->created_at)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->material_no}}</td>
                                <td>{{$item->name}}</td>
                                <td>{{$item->hname}}</td>
                                <td>{{$item->rqty}}</td>
                              </tr>
                            @endif
                          @endforeach
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
                  @endfor
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
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
                <a class="btn btn-info" href="{{ route('receive.print', $receive['receive_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('receive') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('receive.edit', $receive['receive_id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('return.add', $receive['receive_id']) }}" class="btn btn-primary">Return</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
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
                    {{-- <tr><td><b>Date:</b> {{$receive['purchase_date']}}</td></tr> --}}
                    <tr><td><b>Required Date:</b> {{\Carbon\Carbon::parse($receive['require_date'])->format('d-m-Y')}}</td></tr>
                    <tr><td><b>Received Date:</b> {{\Carbon\Carbon::parse($receive['receive_date'])->format('d-m-Y')}}</td></tr>
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
                      <th>Inspection Date</th>
                      {{-- <th>Code</th>
                      <th>Material</th> --}}
                      <th>Material / Product</th>
                      <th>Units / Size</th>
                      <th>Receive Qty</th>
                      <th>Pending</th>
                      <th>Approved</th>
                      <th>Rejected</th>
                      {{-- <th>Inspection Status</th> --}}
                    </tr>
                  </thead>
                  <tbody>
                    @if($receiveMaterial->count())
                      @php $loopIndex = 1; @endphp
                      @foreach($receiveMaterial as $item)
                        @if($item->quantity)
                        <tr>
                          <td>{{$loopIndex++}}</td>
                          <td>{{\Carbon\Carbon::parse($item->inspection_date)->format('d-m-Y')}}</td>
                          {{-- <td>{{$item->material_no}}</td>
                          <td>{{$item->name}}</td> --}}
                          <td>
                            @if($receive['purchase_type'] == 'product')
                              {{ $item->article_no ?? '' }} - {{ $item->sname ?? '' }}
                            @else
                              {{ $item->material_no ?? '' }} - {{ $item->name ?? '' }}
                            @endif
                          </td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->quantity}}</td>
                          <td>{{$item->pending_qty}}</td>
                          <td>{{$item->approved_qty}}</td>
                          <td>{{$item->rejected_qty}}</td>
                          {{-- <td>@if($item->inspection_status == 1) Pending
                          @elseif($item->inspection_status == 2) Approved
                          @else Rejected @endif</td> --}}
                        </tr>
                        @endif
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Inspection Date</th>
                      {{-- <th>Code</th>
                      <th>Material</th> --}}
                      <th>Material / Product</th>
                      <th>Units / Size</th>
                      <th>Receive Qty</th>
                      <th>Pending</th>
                      <th>Approved</th>
                      <th>Rejected</th>
                      {{-- <th>Inspection Status</th> --}}
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
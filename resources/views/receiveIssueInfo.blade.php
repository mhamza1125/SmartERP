@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Issuance Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('rstock.edit', $issue['stock_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-7">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Employee:</b> {{$issue['employee_no']}} - {{$issue['name']}}</td></tr>
                  <tr><td><b>Department:</b> {{$issue['name']}}</td></tr>
                  <tr><td><b>Date:</b> {{$issue['stock_date']}}</td></tr>
                  @if($issue['description'])<tr><td><b>Detail:</b></td></tr>
                  <tr><td>@php echo $issue['description'] @endphp</td></tr>@endif
                </tbody>
              </table>
            </div>
            <div class="col-md-5">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Issuance.#:</b> {{$issue['stock_no']}}</td></tr>
                  <tr><td><b>Issue Date:</b> {{$issue['sdate']}}</td></tr>
                  <tr><td><b>Job.#:</b> {{($issue['job_no'])? $issue['job_no']:'Default Purchase'}}</td></tr>
                  <tr><td><b>Receive Date:</b> {{$issue['stock_date']}}</td></tr>
                </tbody>
              </table>
            </div> 
            <div class="col-md-12">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    {{-- <th>Product</th> --}}
                    <th>Material / Stage</th>
                    <th>Quantity</th>
                  </tr>
                </thead>
                <tbody>
                  @if($issueItem->count())
                    @foreach($issueItem as $item)
                      @if($item->quantity)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                        {{-- <td>{{$item->pname}} {{$item->sname}}</td> --}}
                        <td>{{($item->name)? $item->name:$item->stage}}</td>
                        <td>{{$item->quantity}} {{$item->uname}}</td>
                      </tr>
                      @endif
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    {{-- <th>Product</th> --}}
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
</section>
@endsection
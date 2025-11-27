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
                <a class="btn btn-info" href="{{ route('receive-issuance.print', $issue['stock_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('receiveIssue') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('rstock.edit', $issue['stock_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    @if($issue['table_name'] == 'employee')
                    <tr><td><b>Employee:</b> {{$issue['employee_no']}} - {{$issue['name']}}</td></tr>
                    <tr><td><b>Department:</b> {{$issue['hname']}}</td></tr>                  
                    @else
                    <tr><td><b>Vendor:</b> {{$issue['vendor_no']}} - {{$issue['fname']}}</td></tr>
                    @endif
                    <tr><td><b>Receive Date:</b> {{$issue['stock_date']}}</td></tr>
                    @if($issue['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td>@php echo $issue['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Receive Issuance.#:</b> {{$issue['stock_no']}}</td></tr>
                    <tr><td><b>Issue Date:</b> {{$issue['sdate']}}</td></tr>
                    <tr><td><b>Job.#:</b> {{($issue['job_no'])? $issue['job_no']:'Default Purchase'}}</td></tr>
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
                      <th>Article No</th>
                      <th>Material / Stage</th>
                      <th>Work Done</th>
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
                          <td>{{($item->name)? $item->name:$item->stage}}</td>
                          <td>
                            @foreach(explode('|', $item->work_logs) as $index => $work)
                              @php
                                $headName = $head->firstWhere('head_id', $work)?->name;
                              @endphp
                              @if($headName)
                                @if($index > 0)<span>, </span>@endif
                                <span>{{ $headName }}</span>
                              @else
                                <span> None </span>
                              @endif
                            @endforeach
                          </td>
                          <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                        </tr>
                        @endif
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Material / Stage</th>
                      <th>Work Done</th>
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
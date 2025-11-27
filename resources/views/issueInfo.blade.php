@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-print"></i> Print
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('issuance.print', $issue['stock_id']) }}" target="_blank">
                      <i class="fas fa-file-alt"></i> Issuance Record
                    </a>
                    @for($i=1; $i<=$count; $i++)
                      <a class="dropdown-item" href="{{ route('issuance.print', $issue['stock_id']) }}?receive={{ $i }}" target="_blank">
                        <i class="fas fa-file-alt"></i> {{ $totalTimes[$i-1]['stock_no'] }}
                      </a>
                    @endfor
                  </div>
                </div>
              </div>
              <div class="btn-group">
                <a href="{{ route('issue') }}" class="btn btn-primary">Back</a>
                @if(!$issue['has_received'])
                  <a href="{{ route('stock.edit', $issue['stock_id']) }}" class="btn btn-primary">Edit</a>
                @endif
                <a href="{{ route('rstock.add', $issue['stock_id']) }}" class="btn btn-primary">Receive</a>
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
                    <tr><td><b>Job.#:</b> {{($issue['job_no'])? $issue['job_no']:'Default Purchase'}}</td></tr>
                    <tr><td><b>Issued For:</b> {{$issue['sname']}}</td></tr>
                    {{-- <tr><td><b>Date:</b> {{$issue['stock_date']}}</td></tr> --}}
                  </tbody>
                </table>
              </div> 
            </div>
            <div class="row">
              <div class="col-md-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Issuance</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">Receive-All</a>
                  </li>
                  @if($count > 1)
                    @for($i=1; $i<=$count; $i++)
                      <li class="nav-item">
                        <a class="nav-link" id="tab-{{ $i }}" data-toggle="tab" href="#tab-content-{{ $i }}" role="tab" aria-controls="tab-content-{{ $i }}" aria-selected="false">{{$totalTimes[$i-1]['stock_no']}}</a>
                      </li>
                    @endfor
                  @endif
                </ul>
                
                <div class="tab-content" id="myTabContent">
                  {{-- Issuance --}}
                  <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Material / Stage</th>
                          <th>Quantity</th>
                          <th>Average</th>
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
                              <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                              <td>{{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : $item->quantity }} {{$item->puname}}</td>
                              {{-- <td>{{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0' }} Units</td> --}}
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
                          <th>Quantity</th>
                          <th>Average</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
  
                  {{-- Receive All --}}
                  <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab"> 
                    @if($count == 1) <a href="{{ route('rstock.edit', $totalTimes[0]['stock_id'] )}}" class="btn btn-primary rounded-pill pbtn" target="_blank">Edit</a> @endif
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
                        @if($issueSum->count())
                          @foreach($issueSum as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                              <td>{{($item->name)? $item->name:$item->stage}}</td>
                              <td>{{$item->total_quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                            </tr>
                          @endforeach
                        @else
                          <tr>
                            <td valign="top" colspan="4" class="dataTables_empty text-center">No data available in table</td>
                          </tr>
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
  
                  {{-- Receive Times --}}
                  @if($count > 1)
                    @for($i=1; $i<=$count; $i++)  
                      @php $loopIndex = 1; @endphp
                      <div class="tab-pane fade" id="tab-content-{{ $i }}" role="tabpanel" aria-labelledby="tab-{{ $i }}">
                        <a href="{{ route('rstock.edit', $totalTimes[$i-1]['stock_id'] )}}" class="btn btn-primary rounded-pill pbtn" target="_blank">Edit</a>
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
                            @foreach($issueAll as $item)
                              @if($totalTimes[$i-1]['stock_no'] == $item->stock_no)
                              <tr>
                                <td>{{$loopIndex++}}</td>
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
                                <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}    </td>
                              </tr>
                              @endif
                            @endforeach
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
                    @endfor
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Wages Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('stock.add') }}" class="btn btn-primary">Pay</a>
              </div>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-7">
              <table class="table table-sm">
                <tbody>
                  @if($issue['table_name'] == 'employee')
                  <tr><td><b>Employee:</b> {{$issue['employee_no']}} - {{$issue['name']}}</td></tr>
                  <tr><td><b>Department:</b> {{$issue['hname']}}</td></tr>                  
                  @else
                  <tr><td><b>Vendor:</b> {{$issue['vendor_no']}} - {{$issue['fname']}}</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
            <div class="col-md-5">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Month:</b> {{date("F Y", strtotime($issue['stock_date']))}}</td></tr>
                  <tr><td><b>Total Wages:</b> {{ number_format($totalWages) }} Rupee</td></tr>
                </tbody>
              </table>
              </div>
            <div class="col-md-12">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Article</th>
                    <th>Product Stage</th>
                    <th>Work Done</th>
                    <th>Quantity</th>
                    <th>Wages</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @if($wages->count())
                    @foreach($wages as $item)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                        <td>{{$item->stage}}</td>
                        <td>
                          @foreach(explode('|', $item->work_logs) as $index => $work)
                            @php
                              $headName = $head->firstWhere('head_id', $work)?->name;
                            @endphp
                            @if($headName)
                              @if($index > 0)<span>, </span>@endif
                              <span>{{ $headName }}</span>
                            @endif
                          @endforeach
                        </td>
                        <td>{{$item->quantity}} {{$item->uname}}</td>
                        <td>{{$item->total_wages}}</td>
                        <td>{{$item->stock_date}}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Article</th>
                    <th>Product Stage</th>
                    <th>Work Done</th>
                    <th>Quantity</th>
                    <th>Wages</th>
                    <th>Date</th>
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
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
                <a href="{{ route('wages') }}" class="btn btn-primary">Back</a>
                @if($issue['table_name'] == 'employee')
                  <a href="{{ route('transaction.addEPayment') }}" class="btn btn-primary">Pay</a>
                @else
                  <a href="{{ route('transaction.addVPayment') }}" class="btn btn-primary">Pay</a>
                @endif
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('wages.filter', $issue['stock_id']) }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
              <div class="row">
                <div class="form-group col-md-5">                    
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-5">                    
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-2 mt-4">     
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                </div>
              </div>
            </form>
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
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    @if(!empty($dfrom) && !empty($dto))
                      <tr><td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td></tr>
                      <tr><td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td></tr>
                    @else
                      <tr><td><b>Month:</b> {{date("F Y", strtotime($issue['stock_date']))}}</td></tr>
                    @endif
                    <tr><td><b>Total Wages:</b> {{ number_format($totalWages) }} Rupee</td></tr>
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
                      <th>Date</th>
                      <th>Article</th>
                      <th>Product Stage</th>
                      <th>Work Done</th>
                      <th>Quantity</th>
                      <th>Wages</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($wages->count())
                      @foreach($wages as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->stock_date}}</td>
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
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Article</th>
                      <th>Product Stage</th>
                      <th>Work Done</th>
                      <th>Quantity</th>
                      <th>Wages</th>
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
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Wages - Employee / Vendor</h4>
            <div class="card-header-action">
              <a href="{{ route('stock.add') }}" class="btn btn-primary">Pay Wages</a>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Employee Wages</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">Vendor Wages</a>
              </li>
            </ul> 
            
            <div class="tab-content" id="myTabContent">
              {{-- Employee Wages --}}
              <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="tableExport" style="width:100%;">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Employee</th>
                        <th>Wages (Pkr)</th>
                        <th>Month</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(!empty($employeeWages))
                        @foreach($employeeWages as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->employee_no}} - {{$item->name}}</td>
                          <td>{{ number_format($item->total_wages) }}</td>
                          <td>{{$item->month_year}}</td>
                          <td><a href="{{ route('wages.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a></td>     
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Employee</th>
                        <th>Wages (Pkr)</th>
                        <th>Month</th>
                        <th>Action</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              {{-- Vendor Wages --}}
              <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="tableExport1" style="width:100%;">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Vendor</th>
                        <th>Wages (Pkr)</th>
                        <th>Month</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if(!empty($vendorWages))
                        @foreach($vendorWages as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                          <td>{{ number_format($item->total_wages) }}</td>
                          <td>{{$item->month_year}}</td>
                          <td><a href="{{ route('wages.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a></td>            
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Employee</th>
                        <th>Wages (Pkr)</th>
                        <th>Month</th>
                        <th>Action</th>
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
  </div>
</section>
@endsection
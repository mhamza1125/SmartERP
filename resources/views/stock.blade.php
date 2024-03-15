@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Available Stock Table</h4>
            <div class="card-header-action">
              <a href="{{ route('stock.add') }}" class="btn btn-primary">Issue Material</a>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Material Stock</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">Product Stock</a>
              </li>
            </ul> 
            
            <div class="tab-content" id="myTabContent">
              {{-- Material Stock --}}
              <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
                <div class="table-responsive">
                  <table class="table table-sm table-striped">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($stock->count())
                        @foreach($stock as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->material_no}}</td>
                          <td>{{$item->name}}</td>
                          <td>{{$item->total_received + $item->stockIn - $item->stockOut - $item->total_returned}} {{$item->uname}}</td>                  
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material Name</th>
                        <th>Quantity</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              {{-- Issuance --}}
              <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                <div class="table-responsive">
                  <table class="table table-sm table-striped">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Prouct</th>
                        <th>Stage</th>
                        <th>Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($pstock->count())
                        @foreach($pstock as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                          <td>{{$item->stname}}</td>
                          <td>{{$item->stockIn - $item->stockOut}} {{$item->uname}}</td>                  
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Prouct</th>
                        <th>Stage</th>
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
  </div>
</section>
@endsection
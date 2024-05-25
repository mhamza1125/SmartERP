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
              <li class="nav-item">
                <a class="nav-link" id="machine-tab" data-toggle="tab" href="#machine" role="tab" aria-controls="machine" aria-selected="false">Machine Material</a>
              </li>
            </ul> 
            
            <div class="tab-content" id="myTabContent">
              {{-- Material Stock --}}
              <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="tableExport" style="width:100%;">                    
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
                        @php $loopIndex = 1; @endphp
                        @foreach($stock as $item)
                          @unless($item->material_type_id == 101)
                          <tr>
                            <td>{{$loopIndex++}}</td>
                            <td>{{$item->material_no}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned)}} {{$item->uname}}</td>                  
                          </tr>
                          @endunless
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
              {{-- Product Stock --}}
              <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="tableExport1" style="width:100%;">                    
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Article No</th>
                        <th>Item / Product</th>
                        <th>Size</th>
                        <th>Stage</th>
                        <th>Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($pstock->count())
                        @php $product_id = 0; $size = 0; @endphp
                        @foreach($pstock as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          @if($item->product_id == $product_id)
                            <td colspan="2"></td>
                          @else
                            <td>{{$item->article_no}}</td>
                            <td>{{$item->name}}</td>
                          @endif
                          @if($item->sname == $size && $item->product_id == $product_id)
                            <td></td>
                          @else
                            <td>{{$item->sname}}</td>
                          @endif
                          {{-- <td>{{$item->article_no}} - Size {{$item->sname}}</td> --}}
                          <td>{{$item->stname}}</td>
                          <td>{{number_format($item->stockIn - $item->stockOut)}} {{$item->uname}}</td>                  
                        </tr>
                        @php $product_id = $item->product_id; $size = $item->sname @endphp
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Article No</th>
                        <th>Item / Product</th>
                        <th>Size</th>
                        <th>Stage</th>
                        <th>Quantity</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              {{-- Material Stock --}}
              <div class="tab-pane fade" id="machine" role="tabpanel" aria-labelledby="machine-tab">      
                <div class="table-responsive">
                  <table class="table table-sm table-striped" id="tableExport" style="width:100%;">                    
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
                        @php $loopIndex = 1; @endphp
                        @foreach($stock as $item)
                          @unless($item->material_type_id != 101)
                          <tr>
                            <td>{{$loopIndex++}}</td>
                            <td>{{$item->material_no}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned)}} {{$item->uname}}</td>                  
                          </tr>
                          @endunless
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
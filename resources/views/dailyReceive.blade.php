@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Daily Receiving</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('rstock.filter') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
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
            @if(!empty($dfrom) && !empty($dto))
            <div class="row">
              <div class="col md-12">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td colspan="2"></td>
                      <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                      <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">              
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
                    @if($issueItem->count())
                    @php $index = 1; $product_id = ''; $size = ''; @endphp
                      @foreach($issueItem as $item)
                        <tr>
                          <td>{{$index++}}</td>
                          @if($item->product_id == $product_id)
                              <td colspan="2"></td>
                          @else
                            <td>{{$item->article_no}}</td>
                            <td>{{$item->pname}}</td>
                          @endif
                          @if($item->sname == $size)
                            <td></td>
                          @else
                            <td>{{$item->sname}}</td>
                          @endif
                          <td>{{$item->stname}}</td>
                          <td>{{$item->stockIn}} {{$item->puname}}</td>
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
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
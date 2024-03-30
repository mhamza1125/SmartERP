@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Material Info</h4>
            <div class="card-header-action">
              <a href="{{ route('productMaterial.edit', $productType['product_type_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <h6>Product Packing</h6>
            <table class="table table-sm">
              <tbody>
                <tr>
                  <td><b>Article No: </b> {{$productType['article_no']}} - Size {{$productType['hname']}}</td>
                  <td><b>Quantity in Box: </b> {{$productBox['quantity']}} {{$productType['uname']}}</td>
                  <td><b>Box: </b> {{$productBox['box_no']}} - {{$productBox['name']}}</td>
                </tr>
                <tr>
                  <td><b>Box Type: </b> {{$productBox['hname']}}</td>
                  <td><b>Box Dimension: </b> {{$productBox['length']}} x {{$productBox['width']}} x {{$productBox['height']}} cms</td>
                  <td><b>Box Weight: </b> {{$productBox['weight']}} Grams</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $productBox['description'] @endphp
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <h6>Product Material</h6>
            <table class="table table-striped table-sm" style="width:100%;">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Material</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                </tr>
              </thead>
              <tbody>
                <tr class="trow">
                  <th></th>
                  <th>Article No: &nbsp {{$productType['article_no']}}</th>
                  <th>Size: &nbsp {{$productType['hname']}}</th>
                  <th></th>
                </tr>
                @if($productMaterial->count())
                  @foreach($productMaterial as $item)
                  <tr>
                    <td>{{$loop->index + 1}}</td>
                    <td>{{$item->name}}</td>
                    <td>{{$item->quantity}}</td>
                    <td>{{$item->hname}}</td>
                  </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
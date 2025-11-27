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
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('productMaterial.print', $productType['product_type_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('productMaterial') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('productMaterial.edit', $productType['product_type_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <h6>Product Packing</h6>
            <table class="table table-sm">
              <tbody>
                @php $item = $productMaterial->firstWhere('material_type_id', '61'); @endphp
                @if($item)
                  <tr>
                    <td><b>Article No: </b> {{$productType['article_no']}}</td>
                    <td><b>Product Name: </b> {{$productType['name']}}</td>
                    <td><b>Size: </b> {{$productType['hname']}}</td>
                  </tr>
                  <tr>
                    <td><b>Box No: </b> {{$item->material_no}}</td>
                    <td><b>Box Name: </b> {{$item->name}}</td>
                    <td><b>Quantity in Box: </b> {{1/$item->quantity}} {{$productType['uname']}}</td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <div class="row">
                        <div class="col-md-1"><b>Details: </b></div>
                        <div class="col-md-11">
                          @php echo $item->description @endphp
                        </div>
                      </div>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
            
            <h6>Product Material</h6>
            <table class="table table-striped table-sm" style="width:100%;">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Material No</th>
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
                  <th></th>
                </tr>
                @if($productMaterial->count())
                  @php $count = 1;  @endphp
                  @foreach($productMaterial as $item)
                    @unless($item->material_type_id == '61')
                      <tr>
                        <td>{{$count++}}</td>
                        <td>{{$item->material_no}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{$item->hname}}</td>
                      </tr>
                    @endunless
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
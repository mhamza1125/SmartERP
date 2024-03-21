@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Cost Info</h4>
            <div class="card-header-action">
              <a href="{{ route('productCost.edit', $productType['product_type_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table" id="tableExport" style="width:100%;">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Cost Head</th>
                  <th>Price</th>
                </tr>
              </thead>
              <tbody>
                <tr class="trow">
                  <th></th>
                  <th>Article No: &nbsp {{$productType['article_no']}}</th>
                  <th>Size: &nbsp {{$productType['hname']}}</th>
                </tr>
                @if($productCost->count())
                  @foreach($productCost as $item)
                  <tr>
                    <td>{{$loop->index + 1}}</td>
                    <td>{{$item->hname}}</td>
                    <td>{{$item->amount}}</td>
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
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
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('productCost.edit', $product['product_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table" id="tableExport" style="width:100%;">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Employee / Vendor</th>
                  <th>Cost Head</th>
                  <th>Price</th>
                </tr>
              </thead>
              <tbody>
                <tr class="trow">
                  <th></th>
                  <th>Article No: &nbsp {{$product['article_no']}}</th>
                  <th>Article: &nbsp {{$product['name']}}</th>
                  <th></th>
                </tr>
                @if($productCost->count())
                  @foreach($productCost as $item)
                  <tr>
                    <td>{{$loop->index + 1}}</td>
                    <td>{{$item->employee_no ?? $item->vendor_no}}{{$item->name ? ' - '.$item->name : 'General Cost'}}</td>
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
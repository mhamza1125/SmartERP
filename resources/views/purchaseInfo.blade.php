@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Purchase Info</h4>
            <div class="card-header-action">
              <a href="{{ route('purchase.edit', $purchase['purchase_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body row">
            <div class="col-md-7">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>Name:</b> {{$purchase['fname']}}</td></tr>
                  <tr><td><b>Phone:</b> {{$purchase['phone1']}}</td></tr>
                  <tr><td><b>Address:</b> {{$purchase['address']}}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-5">
              <table class="table table-sm">
                <tbody>
                  <tr><td><b>P.O.#:</b> {{$purchase['purchase_no']}}</td></tr>
                  <tr><td><b>Date:</b> {{$purchase['purchase_date']}}</td></tr>
                  <tr><td><b>Required Date:</b> {{$purchase['require_date']}}</td></tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-12 mt-2">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Code</th>
                    <th>Material</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  @if($purchaseItem->count())
                    @foreach($purchaseItem as $item)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{$item->material_no}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{$item->price}}</td>
                        <td>{{$item->quantity * $item->price}}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  @php $total = $purchaseItem->sum(function($item) {
                    return $item->quantity * $item->price;
                  }); @endphp
                  <tr>
                    <th colspan="3"></th>
                    <th colspan="2" class="text-center">Grand Total:</th>
                    <th>{{ $total }}</th>
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
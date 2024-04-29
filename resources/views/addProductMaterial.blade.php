@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Product Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('productMaterial.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product</label>
                    <input type="text" readonly class="form-control" value="{{$product['article_no']}} - {{$product['name']}}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Size</label>
                    <select class="form-control select2" name="product_type_id" required>
                      <option value="" selected disabled>Select Product Sizes</option>
                      @if($productType->count())
                        @foreach($productType as $item)
                          <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>Size {{$item->hname}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Product</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Material</th>
                        <th>Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($material->count())
                        @foreach($material as $item)
                          <tr>
                            <td>{{$loop->index + 1}}</td>
                            <td>
                              {{$item->material_no}} - {{$item->name}}
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}" required>
                            </td>
                            <td class="form-group">
                                <input type="number" min="0" class="form-control" name="quantity[]" placeholder="0" required>
                            </td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>

              <h6>Product Packing</h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Material Boxes</label>
                    <select class="form-control select2" name="material_id[]" required>
                      <option value="" disabled selected>Select Boxes</option>
                      @if($mbox->count())
                        @foreach($mbox as $item)
                          <option value="{{$item->material_id}}" {{ old('material_id') == $item->material_id ? 'selected' : '' }}>{{$item->material_no}} - {{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Unit in Box</label>
                    <input type="number" min="0" class="form-control" placeholder="0" value="" required id="bqty">
                    <input type="hidden" class="form-control" name="quantity[]" placeholder="0" value="" required id="mqty">
                  </div>
                </div>
              </div>
              
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script> var isPMPage = false; </script>
@endsection
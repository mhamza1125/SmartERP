@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Product Material</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('productMaterial.update', $productType['product_type_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product</label>
                    <input type="text" readonly class="form-control" value="{{$productType['article_no']}} - {{$productType['name']}}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Size</label>
                    <input type="text" readonly class="form-control" value="Size - {{$productType['hname']}}">
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
                      @if($productMaterial->count())
                        @foreach($productMaterial as $item)
                          @unless($item->material_type_id == '61')
                          <tr data-item-id="{{ $item->product_type_id }}">
                            <td>{{$loop->index + 1}}</td>
                            <td>{{$item->material_no}} - {{$item->name}}
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
                            </td>
                            <td class="form-group"><input type="number" min="0" step="0.0000000001" class="form-control" name="quantity[]" value="{{$item->quantity}}" required>
                            </td>
                          </tr>
                          @endunless  
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
                      @foreach($mbox as $item)
                        @php 
                          $selected = old('material_id') == $item->material_id ? 'selected' : '';
                          $item2 = $productMaterial->firstWhere('material_type_id', '61'); 
                          if($item2 && $item2->material_id == $item->material_id) {
                              $selected = 'selected';}
                        @endphp
                        <option value="{{$item->material_id}}" {{ $selected }}>{{$item->material_no}} - {{$item->name}}</option>
                      @endforeach
                    </select>                  
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                      <label>Product Unit in Box</label>
                      @php $item = $productMaterial->firstWhere('material_type_id', '61'); @endphp
                      <input type="number" min="0" class="form-control" placeholder="0" value="{{ $item ? 1/$item->quantity : '' }}" required id="bqty">
                      <input type="hidden" class="form-control" name="quantity[]" placeholder="0" value="{{ $item ? $item->quantity : '' }}" required id="mqty">
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
<script> var isPMPage = true; </script>
@endsection
@extends('index')

@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Product Cost</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('productCost.update', $productType['product_type_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Products</label>
                    <input type="text" class="form-control" readonly value="{{ $productType['article_no'] }} Size - {{$productType['hname']}}">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Costing Heads</label>
                    <select class="form-control select2" name="head_id">
                      <option value="" disabled selected>Select Head</option>
                      @if($head->count())
                        @foreach($head as $item)
                          <option value="{{$item->head_id}}" {{ old('head_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Wages</label>
                    <input type="number" min="0" class="form-control" name="amount" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtn" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Costing Head</th>
                        <th>Wages</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($productCost->count())
                        @foreach($productCost as $item)
                          <tr data-item-id="{{ $item->product_type_id }}">
                            <td></td>
                            <td>{{$item->hname}}
                              <input type="hidden" name="head_id[]" value="{{$item->head_id}}">
                            </td>
                            <td>{{$item->amount}}
                              <input type="hidden" name="amount[]" value="{{$item->amount}}"></td>
                            </td>
                            <td><button class="deleteRowBtn btn btn-danger">X</button></td>
                          </tr>
                        @endforeach
                      @endif
                      <!-- Table rows will be dynamically added here -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script> var isProductCostPage = true; </script>  
@endsection

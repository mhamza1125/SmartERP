@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Product</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('product.update', $product['product_id']) }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Category</label>
                    <select class="form-control select2" name="category_id" required>
                      <option value="" selected disabled>Select Category</option>
                      @if($category->count())
                        @foreach($category as $item)
                          <option value="{{$item->category_id}}" {{ $product['category_id'] == $item->category_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Category</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Status</label>
                    <select class="form-control" name="product_status" required>
                      <option value="1" {{ $product['product_status'] == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ $product['product_status'] == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Aricle No</label>
                    <input type="text" class="form-control" name="article_no" required value="{{$product['article_no']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Article No</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" class="form-control" name="name" required value="{{$product['name']}}">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Product Name</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Sizes</label>
                    <select class="form-control select2" name="size_id[]" multiple="" required>
                      <option value="" disabled>Select Sizes</option>
                      @if($size->count())
                        @foreach($size as $item)
                            <option value="{{ $item->head_id }}" {{ $productType->pluck('head_id')->contains($item->head_id) ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Select Sizes</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>File / Images</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="customFile" name="image[]" multiple>
                      <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                    <div class="valid-feedback" id="fileSuccess">Good job!</div>
                    <div class="invalid-feedback" id="fileError"></div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{$product['description']}}</textarea>
                  </div>
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
@endsection
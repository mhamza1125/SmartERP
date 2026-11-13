@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Products Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Product::class)<a href="{{ route('product.add') }}" class="btn btn-primary">Add Product</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Category</th>
                    <th>Article No</th>
                    <th>Product Name</th>
                    <th>Sizes</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($product->count())
                    @foreach($product as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->cname}}</td>
                      <td>{{$item->article_no}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->sizes ?: '-'}}</td>
                      <td>
                        @if($item->product_status)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-danger">Inactive</span>
                        @endif
                      </td>                 
                      <td>
                        @if(auth()->user()->can('show', App\Models\Product::class))<a href="{{ route('product.show', $item->product_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Product::class)<a href="{{ route('product.edit', $item->product_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Product::class)@include('deleteButton', ['url' => route('product.destroy', $item->product_id), 'check' => route('product.deleteCheck', $item->product_id), 'label' => 'Product '.$item->article_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                        @if(auth()->user()->can('show', App\Models\Product::class))<a href="{{ route('product.ptc', $item->product_id) }}" class="btn btn-warning btn-sm">PTC</a>@endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Category</th>
                    <th>Article No</th>
                    <th>Product Name</th>
                    <th>Sizes</th>
                    <th>Status</th>
                    <th>Action</th>
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
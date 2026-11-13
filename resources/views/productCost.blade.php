@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Cost Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Product::class)<a href="{{ route('productCost.add') }}" class="btn btn-primary">Add Product Cost</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    <th>Product Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($productCost->count())
                    @foreach($productCost as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->article_no}}</td>
                      <td>{{$item->name}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Product::class))<a href="{{ route('productCost.show', $item->product_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Product::class)<a href="{{ route('productCost.edit', $item->product_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Article No</th>
                    <th>Product Name</th>
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
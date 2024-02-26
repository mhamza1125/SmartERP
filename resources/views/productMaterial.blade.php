@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Material Table</h4>
            <div class="card-header-action">
              <a href="{{ route('productMaterial.add') }}" class="btn btn-primary">Add Product Material</a>
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
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($productMaterial->count())
                    @foreach($productMaterial as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->article_no}} - Size {{$item->hname}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{(new DateTime($item->created_at))->format('Y-m-d') }}</td>                      
                      <td>
                        <a href="{{ route('productMaterial.show', $item->product_type_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('productMaterial.edit', $item->product_type_id) }}" class="btn btn-primary btn-sm">Edit</a>
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
                    <th>Date</th>
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
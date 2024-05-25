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
              <a href="{{ route('productCost.add') }}" class="btn btn-primary">Add Product Cost</a>
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
                  @if($productCost->count())
                    @foreach($productCost as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->article_no}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{(new DateTime($item->created_at))->format('Y-m-d') }}</td>                      
                      <td>
                        <a href="{{ route('productCost.show', $item->product_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('productCost.edit', $item->product_id) }}" class="btn btn-primary btn-sm">Edit</a>
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
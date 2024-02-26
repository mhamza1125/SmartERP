@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Category Table</h4>
            <div class="card-header-action">
              <a href="{{ route('category.add') }}" class="btn btn-primary">Add Category</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Category Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($category->count())
                    @foreach($category as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>                      
                      <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->category_id}}">Edit</button></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Category Name</th>
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
@if($category->count())
  @foreach($category as $item)
    <div class="modal fade" id="exampleModal{{$item->category_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Category</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('category.update', $item->category_id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
              <div class="card-body">
                <div class="form-group">
                  <label>Category Name</label>
                  <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Category Name</div>
                </div>
              </div>
              <div class="card-footer text-right">
                <button class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif
@endsection
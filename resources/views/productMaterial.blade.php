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
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Product Material</a>
              {{-- <a href="{{ route('productMaterial.add') }}" class="btn btn-primary">Add Product Material</a> --}}
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
                  @if($productMaterial->count())
                    @foreach($productMaterial as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->article_no}} - Size {{$item->hname}}</td>
                      <td>{{$item->name}}</td>
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

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="formModal"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModal">Add Receive Issuance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('productMaterial.add', 0) }}" method="POST" class="needs-validation" novalidate="" id="productMaterial"> @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Select Product</label>
              <select class="form-control select2" name="product_id" id="product_id" required style="width: 100%">
                <option value="" selected disabled>Select Product Material</option>
                @if($product->count())
                  @foreach($product as $item)
                    <option value="{{$item->product_id}}">{{$item->article_no}} - {{$item->name}}</option>
                  @endforeach
                @endif
              </select>
            </div>
            <div class="form-group text-right">
              <button class="btn btn-primary" onclick="updateFormAction()">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function updateFormAction() {
        var stockId = document.getElementById('product_id').value;
        document.getElementById('productMaterial').action = "{{ route('productMaterial.add', ':stockId') }}".replace(':stockId', stockId);
        document.getElementById('productMaterial').submit();
    }
</script>
@endsection
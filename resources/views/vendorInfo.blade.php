@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Vendor Info</h4>
            <div class="card-header-action">
              <div class="btn-group">=
                <a class="btn btn-info" href="{{ route('vendor.print', $vendor['vendor_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('vendor') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('vendor.edit', $vendor['vendor_id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('vendor.detail', $vendor['vendor_id']) }}" class="btn btn-primary">Ledger</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Vendor No: </b> {{ $vendor['vendor_no'] }}</td>
                  <td><b>Name: </b> {{ $vendor['name'] }}</td>
                  <td><b>Full Name: </b> {{ $vendor['fname'] }}</td>
                </tr>
                <tr>
                  <td><b>Vendor Type: </b> {{ $vendor['vtname'] }}</td>
                  {{-- <td><b>Vendor as Worker: </b>
                    @if($vendor['vendor_type'])
                      <span class="badge badge-success">Active</span>
                    @else
                      <span class="badge badge-danger">Inactive</span>
                    @endif
                  </td> --}}
                  <td><b>City: </b> {{ $vendor['cname'] }}</td>
                  <td><b>Contact No: </b> {{ $vendor['phone1'] }}</td>
                </tr>
                <tr>
                  <td><b>Contact Person: </b> {{ $vendor['cperson'] }}</td>
                  <td><b>Phone No: </b> {{ $vendor['phone2'] }}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{ $vendor['address'] }}, {{ $vendor['cname'] }}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">{!! $vendor['description'] !!}</div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            @if($material->count())
              <h5>Vendor Materials</h5>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Material Type</th>
                    <th>Material No</th>
                    <th>Material Name</th>
                    <th>Unit</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($material as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $item->mtname }}</td>
                      <td>{{ $item->material_no }}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->uname }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <blockquote>No Materials</blockquote>
            @endif

            @if(isset($product) && $product->count())
              <h5>Vendor Products</h5>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Category</th>
                    <th>Article No</th>
                    <th>Product Name</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($product as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $item->cname }}</td>
                      <td>{{ $item->article_no }}</td>
                      <td>{{ $item->name }}</td>
                      <td>
                        @if($item->product_status == 1)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-danger">Inactive</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <blockquote>No Products</blockquote>
            @endif

            @if($image->count())
              <h5>Images</h5>
              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                @foreach($image as $item)
                  <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <a href="{{ URL::asset('resources/vendor/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/vendor/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'vendor'])}}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                    </form>
                  </div>
                @endforeach
              </div>
            @else
              <blockquote> No Images </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

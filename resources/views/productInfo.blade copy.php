@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Info</h4>
            <div class="card-header-action">
              <a href="{{ route('product.edit', $product['product_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Article No: </b> {{$product['article_no']}}</td>
                  <td><b>Product Name: </b> {{$product['name']}}</td>
                  <td><b>Unit: </b> {{$product['hname']}}</td>
                </tr>
                <tr>
                  <td><b>Category: </b> {{$product['cname']}}</td>
                  <td><b>Sizes: </b> @foreach($size as $item) {{$item->name}}, @endforeach </td>
                  <td><b>Current Status: </b> @if($product['product_status']) 
                    <span class="badge badge-success">Active</span> @else 
                    <span class="badge badge-danger">Inactive</span> @endif</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $product['description'] @endphp
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            @if($image->count())
              <h5>Images</h5>
              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                @foreach($image as $item)
                  <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <a href="{{ URL::asset('resources/product/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/product/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'product'])}}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                    </form>
                  </div>
                @endforeach
              </div>
            @else
              <blockquote> No Images </blockquote>
            @endif

            @if($file->count())
              <h5 class="mt-4">Files / Attachments</h5>
              <div class="row">
                @foreach($file as $item)
                  <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
                    <div class="card card-primary">
                      <div class="card-header">
                        <h4>{{($item->file_title)? $item->file_title:'Untitled File'}}</h4>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <a href="{{ asset('resources/product_file/' . $item->image) }}" target="_blank" class="btn btn-info">View</a>
                          </div>
                          <div class="col-md-6">
                            <form action="{{ route('image.delete', ['id' => $item->image_id, 'dir' => 'product_file']) }}" method="POST">
                              @csrf
                              <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <blockquote> No Files / Attachments </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
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
              <a href="{{ route('vendor.edit', $vendor['vendor_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Name: </b> {{$vendor['name']}}</td>
                  <td><b>Full Name: </b> {{$vendor['fname']}}</td>
                  <td><b>Vendor Type: </b> {{$vendor['vtname']}}</td>
                </tr>
                <tr>
                  <td><b>Contact No: </b> {{$vendor['phone1']}}</td>
                  <td><b>Phone No: </b> {{$vendor['phone2']}}</td>
                  <td><b>City: </b> {{$vendor['cname']}}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{$vendor['address']}}, {{$vendor['cname']}}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $vendor['description'] @endphp
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
                    <a href="{{ URL::asset('resources/vendor/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/vendor/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', $item->image_id)}}" method="POST">
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
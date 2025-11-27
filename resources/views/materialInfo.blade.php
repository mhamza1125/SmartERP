@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('material.print', $material['material_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('material') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('material.edit', $material['material_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <table class="table">
                <tbody>
                  <tr>
                    <td><b>Material No: </b> {{$material['material_no']}}</td>
                    <td><b>Material Name: </b> {{$material['name']}}</td>
                    <td><b>Material Unit: </b> {{$material['uname']}}</td>
                  </tr>
                  <tr>
                    <td><b>Material Type: </b> {{$material['mtname']}}</td>
                    <td><b>Current Vendor: </b> {{$material['vendor_no']}} - {{$material['fname']}}</td>
                    <td><b>Current Price (Pkr): </b> {{$material['cprice']}}</td>
                  </tr>
                  <tr>
                    <td><b>Opening Stock: </b> {{$material['quantity'] ?? 0}} {{$material['uname']}}</td>
                    <td><b>Material Location (In Store): </b> {{$material['location']}}</td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <div class="row">
                        <div class="col-md-1"><b>Details: </b></div>
                        <div class="col-md-11">
                          @php echo $material['description'] @endphp
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="row">
              <div class="col-md-12">
                @if($image->count())
                  <h5>Images</h5>
                  <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                    @foreach($image as $item)
                      <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                        <a href="{{ URL::asset('resources/material/'. $item->image) }}">
                          <img class="img-responsive thumbnail" src="{{ URL::asset('resources/material/'. $item->image) }}" alt="">
                        </a>
                        <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'material'])}}" method="POST">
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
    </div>
  </div>
</section>
@endsection
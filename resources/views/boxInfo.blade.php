@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Box Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                <a href="{{ route('box.edit', $box['box_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Box No: </b> {{$box['box_no']}}</td>
                  <td><b>Box Name: </b> {{$box['name']}}</td>
                  <td><b>Material: </b> {{$box['hname']}}</td>
                </tr>
                <tr>
                  <td><b>Dimension (LxWxH): </b> {{$box['length']}} x {{$box['width']}} x {{$box['height']}} Inches</td>
                  <td><b>Box Weight (Grams): </b> {{$box['weight']}}</td>
                  <td><b>Current Status: </b> @if($box['box_status']) 
                    <span class="badge badge-success">Active</span> @else 
                    <span class="badge badge-danger">Inactive</span> @endif</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $box['description'] @endphp
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
                    <a href="{{ URL::asset('resources/box/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/box/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'box'])}}" method="POST">
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
                            <a href="{{ asset('resources/box_file/' . $item->image) }}" target="_blank" class="btn btn-info">View</a>
                          </div>
                          <div class="col-md-6">
                            <form action="{{ route('image.delete', ['id' => $item->image_id, 'dir' => 'box_file']) }}" method="POST">
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
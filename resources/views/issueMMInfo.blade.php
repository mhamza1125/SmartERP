@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('missue.print', $issue['stock_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('missue') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('mstock.edit', $issue['stock_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Employee:</b> {{$issue['employee_no']}} - {{$issue['name']}}</td></tr>
                    <tr><td><b>Department:</b> {{$issue['hname']}}</td></tr>                  
                    <tr><td><b>Issuance Date:</b> {{\Carbon\Carbon::parse($issue['stock_date'])->format('d-m-Y')}}</td></tr>
                    @if($issue['description'])<tr><td><b>Detail:</b></td></tr>
                    <tr><td>@php echo $issue['description'] @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Issuance.#:</b> {{$issue['stock_no']}}</td></tr>
                    <tr><td><b>Machine:</b> {{$issue['machine_no']}}</td></tr>
                    <tr><td><b>Machine Type:</b> {{$issue['mname']}}</td></tr>
                    <tr><td><b>Machine Location:</b> {{$issue['location']}}</td></tr>
                  </tbody>
                </table>
              </div> 
            </div>
            <div class="row">
              <div class="col-md-12">
                @if($image->count())
                  <h5>Images</h5>
                  <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                    @foreach($image as $item)
                      <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                        <a href="{{ URL::asset('resources/issuance/'. $item->image) }}">
                          <img class="img-responsive thumbnail" src="{{ URL::asset('resources/issuance/'. $item->image) }}" alt="">
                        </a>
                        <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'issuance'])}}" method="POST">
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
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Material</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($issueItem->count())
                      @foreach($issueItem as $item)
                        @if($item->quantity)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->name}}</td>
                          <td>{{$item->quantity}} {{$item->hname}}</td>
                        </tr>
                        @endif
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Material</th>
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
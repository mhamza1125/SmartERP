@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Info</h4>
            <div class="card-header-action">
              <a href="{{ route('customer.edit', $customer['customer_id']) }}" class="btn btn-primary">Edit</a>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Customer No: </b> {{$customer['customer_no']}}</td>
                  <td colspan="2"><b>Name: </b> {{$customer['fname']}} {{$customer['lname']}}</td>
                </tr>
                <tr>
                  <td><b>Email: </b> {{$customer['email']}}</td>
                  <td><b>Contact: </b> {{$customer['phone']}}</td>
                  <td><b>Fax No: </b> {{$customer['fax']}}</td>
                </tr>
                <tr>
                  <td colspan="3"><b>Address: </b> {{$customer['address']}}</td>
                </tr>
                <tr>
                  <td colspan="3">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $customer['description'] @endphp
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
                    <a href="{{ URL::asset('resources/customer/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/customer/'. $item->image) }}" alt="">
                    </a>
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'customer'])}}" method="POST">
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
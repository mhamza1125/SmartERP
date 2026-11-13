@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Contractor Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('contractor.print', $vendor['vendor_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                @if(auth()->user()->can('access', App\Models\Vendor::class))<a href="{{ route('vendor') }}" class="btn btn-primary">Back</a>@endif
                @can('contractors_edit', App\Models\Vendor::class)<a href="{{ route('vendor.edit2', $vendor['vendor_id']) }}" class="btn btn-primary">Edit</a>@endcan
@can('contractors_delete', App\Models\Vendor::class)@include('deleteButton', ['url' => route('vendor.destroy', $vendor['vendor_id']), 'check' => route('vendor.deleteCheck', $vendor['vendor_id']), 'label' => 'Contractor '.$vendor['vendor_no'], 'class' => 'btn btn-danger'])@endcan
                @if(auth()->user()->can('contractors_show', App\Models\Vendor::class) && auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('vendor.detail', $vendor['vendor_id']) }}" class="btn btn-primary">Ledger</a>@endif
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Contractor No: </b> {{ $vendor['vendor_no'] }}</td>
                  <td><b>Name: </b> {{ $vendor['name'] }}</td>
                  <td><b>Full Name: </b> {{ $vendor['fname'] }}</td>
                </tr>
                <tr>
                  <td><b>City: </b> {{ $vendor['cname'] }}</td>
                  <td><b>Contact No: </b> {{ $vendor['phone1'] }}</td>
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

            @if($image->count())
              <h5>Images</h5>
              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                @foreach($image as $item)
                  <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <a href="{{ URL::asset('resources/vendor/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/vendor/'. $item->image) }}" alt="">
                    </a>
                    @can('contractors_edit', App\Models\Vendor::class)
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'vendor'])}}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcan
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

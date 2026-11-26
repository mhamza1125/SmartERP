@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Return Table</h4>
            <div class="card-header-action">
              {{-- <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Return</a> --}}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Return No</th>
                    {{-- <th>Receive No</th> --}}
                    {{-- <th>Purchase No</th> --}}
                    <th>Vendor</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($return->count())
                    @foreach($return as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{\Carbon\Carbon::parse($item->return_date)->format('d-m-Y')}}</td>                      
                      <td>{{$item->return_no}}</td>
                      {{-- <td>{{$item->receive_no}}</td> --}}
                      {{-- <td>{{$item->purchase_no}}</td> --}}
                      <td>{{$item->fname}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Returns::class))<a href="{{ route('return.show', $item->return_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Returns::class)<a href="{{ route('return.edit', $item->return_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Returns::class)@include('deleteButton', ['url' => route('return.destroy', $item->return_id), 'check' => route('return.deleteCheck', $item->return_id), 'label' => 'Purchase return '.$item->return_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Return No</th>
                    {{-- <th>Receive No</th> --}}
                    {{-- <th>Purchase No</th> --}}
                    <th>Vendor</th>
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
@endsection
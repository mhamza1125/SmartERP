@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Processing Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Material::class)<a href="{{ route('mprocess.add') }}" class="btn btn-primary">Add Processing</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Processing No</th>
                    <th>Job No</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($purchase->count())
                    @foreach($purchase as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{ formatDate($item->purchase_date) }}</td>               
                      <td>{{$item->purchase_no}}</td>
                      <td>{{($item->job_no)? $item->job_no:'Default Purchase'}}</td>
                      <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                      <td>@if($item->has_received) 
                        <span class="badge badge-success">Received</span> @else 
                        <span class="badge badge-danger">To Receive</span> @endif
                      </td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Material::class))<a href="{{ route('mprocess.show', $item->purchase_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @if(!$item->has_received) @can('edit', App\Models\Material::class)<a href="{{ route('mprocess.edit', $item->purchase_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Material::class)@include('deleteButton', ['url' => route('mprocess.destroy', $item->purchase_id), 'check' => route('mprocess.deleteCheck', $item->purchase_id), 'label' => 'Material processing '.$item->purchase_no, 'class' => 'btn btn-danger btn-sm'])@endcan @endif
                        @can('create', App\Models\Receive::class)<a href="{{ route('receive.add', $item->purchase_id) }}" class="btn btn-success btn-sm">Receive</a>@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Purchase No</th>
                    <th>Job No</th>
                    <th>Vendor</th>
                    <th>Status</th>
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
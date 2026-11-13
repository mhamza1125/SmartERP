@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Material::class)<a href="{{ route('material.add') }}" class="btn btn-primary">Add Material</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Material No</th>
                    <th>Material Type</th>
                    <th>Material Name</th>
                    <th>Unit</th>
                    <th>Current Vendor</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($material->count())
                    @foreach($material as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->material_no}}</td>
                      <td>{{$item->mtname}}</td>
                      <td>{{$item->name}}</td>                      
                      <td>{{$item->uname}}</td>
                      <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Material::class))<a href="{{ route('material.show', $item->material_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Material::class)<a href="{{ route('material.edit', $item->material_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Material::class)@include('deleteButton', ['url' => route('material.destroy', $item->material_id), 'check' => route('material.deleteCheck', $item->material_id), 'label' => 'Material '.$item->material_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Material No</th>
                    <th>Material Type</th>
                    <th>Material Name</th>
                    <th>Unit</th>
                    <th>Current Vendor</th>
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

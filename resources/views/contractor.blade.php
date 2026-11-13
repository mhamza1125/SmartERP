@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Contractor Table</h4>
            <div class="card-header-action">
              @can('contractors_create', App\Models\Vendor::class)<a href="{{ route('vendor.add2') }}" class="btn btn-primary">Add Contractor</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Contractor No</th>
                    <th>Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($vendor->count())
                    @foreach($vendor as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->vendor_no}}</td>
                      <td>{{$item->fname}}</td>
                      <td>
                        @if(auth()->user()->can('contractors_show', App\Models\Vendor::class))<a href="{{ route('vendor.show2', $item->vendor_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('contractors_edit', App\Models\Vendor::class)<a href="{{ route('vendor.edit2', $item->vendor_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('contractors_delete', App\Models\Vendor::class)@include('deleteButton', ['url' => route('vendor.destroy', $item->vendor_id), 'check' => route('vendor.deleteCheck', $item->vendor_id), 'label' => 'Contractor '.$item->vendor_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                        @if(auth()->user()->can('contractors_show', App\Models\Vendor::class) && auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('vendor.detail', $item->vendor_id) }}" class="btn btn-success btn-sm">Ledger</a>@endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Contractor No</th>
                      <th>Name</th>
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
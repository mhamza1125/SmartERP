@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Issuance Material Material Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Stock::class)<a href="{{ route('mstock.add') }}" class="btn btn-primary">Add Issue</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Employee</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($issue->count())
                    @foreach($issue as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{ $item->stock_date ? \Carbon\Carbon::parse($item->stock_date)->format('d-m-Y') : 'N/A' }}</td>
                      <td>{{$item->machine_no}}</td>
                      <td>{{$item->hname}}</td>
                      <td>{{$item->employee_no}} - {{$item->name}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Stock::class))<a href="{{ route('mstock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Stock::class)<a href="{{ route('mstock.edit', $item->stock_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Stock::class)@include('deleteButton', ['url' => route('stock.destroy', $item->stock_id), 'check' => route('stock.deleteCheck', $item->stock_id), 'label' => 'Machine issuance '.$item->stock_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Machine No</th>
                    <th>Machine Type</th>
                    <th>Employee</th>
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
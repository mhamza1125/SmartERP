@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Table</h4>
            <div class="card-header-action">
              @can('create', App\Models\Customer::class)<a href="{{ route('customer.add') }}" class="btn btn-primary">Add Customer</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Customer No</th>
                    <th>Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($customer->count())
                    @foreach($customer as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->customer_no}}</td>
                      <td>{{$item->fname}} {{$item->lname}}</td>
                      <td>
                        @if(auth()->user()->can('show', App\Models\Customer::class))<a href="{{ route('customer.show', $item->customer_id) }}" class="btn btn-info btn-sm">View</a>@endif
                        @can('edit', App\Models\Customer::class)<a href="{{ route('customer.edit', $item->customer_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@can('delete', App\Models\Customer::class)@include('deleteButton', ['url' => route('customer.destroy', $item->customer_id), 'check' => route('customer.deleteCheck', $item->customer_id), 'label' => 'Customer '.$item->customer_no, 'class' => 'btn btn-danger btn-sm'])@endcan
                        @if(auth()->user()->can('show', App\Models\Customer::class) && auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('customer.detail', $item->customer_id) }}" class="btn btn-success btn-sm">Ledger</a>@endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Customer No</th>
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
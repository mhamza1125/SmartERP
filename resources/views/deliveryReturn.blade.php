@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Returns</h4>
            <div class="card-header-action">
              <a href="{{ route('delivery') }}" class="btn btn-primary">View Deliveries</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Return No</th>
                    <th>Return Date</th>
                    <th>Order No</th>
                    <th>Job No</th>
                    <th>Customer</th>
                    <th>Stock No</th>
                    <th>Return Reason</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($returns->count())
                    @foreach($returns as $return)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $return->return_no }}</td>
                      <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td>
                      <td>{{ $return->order_no }}</td>
                      <td>{{ $return->job_no }}</td>
                      <td>{{ $return->fname }} {{ $return->lname }}</td>
                      <td>{{ $return->stock_no }}</td>
                      <td>{{ $return->return_reason }}</td>
                      <td>
                        <a href="{{ route('delivery-return.show', $return->delivery_return_id) }}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ route('delivery-return.edit', $return->delivery_return_id) }}" class="btn btn-sm btn-warning">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="9" class="text-center">No delivery returns found</td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

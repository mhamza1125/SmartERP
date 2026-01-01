@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Packing Lists</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped" id="table-1">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Stock No</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Created Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($packingLists as $index => $packingList)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $packingList->stock_no }}</td>
                    <td>{{ $packingList->order_no }}</td>
                    <td>{{ $packingList->fname }} {{ $packingList->lname }}</td>
                    <td>{{ \Carbon\Carbon::parse($packingList->created_at)->format('d-m-Y') }}</td>
                    <td>
                      <a href="{{ route('packingList.show', $packingList->packing_list_id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </td>
                  </tr>
                  @endforeach
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


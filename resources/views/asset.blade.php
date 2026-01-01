@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Assets</h4>
            <div class="card-header-action">
              <a href="{{ route('asset.add') }}" class="btn btn-primary">Add Asset</a>
            </div>
          </div>
          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Asset Name</th>
                    <th class="text-right">Total Debit</th>
                    <th class="text-right">Total Credit</th>
                    <th class="text-right">Net Value</th>
                    <th>Last Transaction</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($assets as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $item->asset_name }}</td>
                      <td class="text-right">{{ number_format($item->total_debit, 2) }}</td>
                      <td class="text-right">{{ number_format($item->total_credit, 2) }}</td>
                      <td class="text-right">
                        @if($item->net_value > 0)
                          <span class="badge badge-success">{{ number_format($item->net_value, 2) }}</span>
                        @elseif($item->net_value < 0)
                          <span class="badge badge-danger">{{ number_format($item->net_value, 2) }}</span>
                        @else
                          <span class="badge badge-secondary">{{ number_format($item->net_value, 2) }}</span>
                        @endif
                      </td>
                      <td>{{ \Carbon\Carbon::parse($item->last_transaction_date)->format('d-m-Y') ?? 'N/A' }}</td>
                      <td>
                        <a href="{{ route('asset.show', $item->asset_name) }}" class="btn btn-info btn-sm">View Ledger</a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center">No assets found</td>
                    </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  @if($assets->count())
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                      <td colspan="2" class="text-right">Total:</td>
                      <td class="text-right">{{ number_format($assets->sum('total_debit'), 2) }}</td>
                      <td class="text-right">{{ number_format($assets->sum('total_credit'), 2) }}</td>
                      <td class="text-right">
                        <span class="badge badge-success">{{ number_format($assets->sum('net_value'), 2) }}</span>
                      </td>
                      <td colspan="2"></td>
                    </tr>
                  @endif
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


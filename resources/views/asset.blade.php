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
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Amount</th>
                    <th>Attachment</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($assets as $item)
                    <tr>
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{ $item->asset_name }}</td>
                      <td class="text-right">{{ $item->quantity }}</td>
                      <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                      <td>
                        @if($item->attachment)
                          <a href="{{ asset('storage/' . $item->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-download"></i>
                          </a>
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('asset.show', $item->asset_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('asset.edit', $item->asset_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('asset.destroy', $item->asset_id) }}" method="POST" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center">No assets found</td>
                    </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  @if($assets->count())
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                      <td colspan="3" class="text-right">Total Assets Value:</td>
                      <td class="text-right">{{ number_format($assets->sum('amount'), 2) }}</td>
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


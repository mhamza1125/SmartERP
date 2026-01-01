@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Asset Ledger: {{ $assetName }}</h4>
            <div class="card-header-action">
              <a href="{{ route('asset.add') }}?asset_name={{ urlencode($assetName) }}" class="btn btn-primary">Add Transaction</a>
              <a href="{{ route('asset') }}" class="btn btn-secondary">Back</a>
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

            {{-- Asset Summary --}}
            <div class="alert alert-info mb-4">
              <div class="row">
                <div class="col-md-3">
                  <strong>Asset Name:</strong><br>
                  {{ $assetName }}
                </div>
                <div class="col-md-3">
                  <strong>Total Acquisitions (Debit):</strong><br>
                  {{ number_format($transactions->sum('debit'), 2) }}
                </div>
                <div class="col-md-3">
                  <strong>Total Disposals (Credit):</strong><br>
                  {{ number_format($transactions->sum('credit'), 2) }}
                </div>
                <div class="col-md-3">
                  <strong>Net Asset Value:</strong><br>
                  <span class="badge badge-{{ $netValue > 0 ? 'success' : ($netValue < 0 ? 'danger' : 'secondary') }}">
                    {{ number_format($netValue, 2) }}
                  </span>
                </div>
              </div>
            </div>

            {{-- Transaction History Table --}}
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Transaction Type</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                    <th>Attachment</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php $balance = 0; @endphp
                  @forelse($transactions as $transaction)
                    @php
                      $debit = $transaction->debit ?? 0;
                      $credit = $transaction->credit ?? 0;
                      $balance += $debit - $credit;
                    @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') ?? 'N/A' }}</td>
                      <td>
                        @if($transaction->transaction_type == 'purchase')
                          <span class="badge badge-success">Purchase</span>
                        @elseif($transaction->transaction_type == 'sale')
                          <span class="badge badge-warning">Sale</span>
                        @elseif($transaction->transaction_type == 'depreciation')
                          <span class="badge badge-info">Depreciation</span>
                        @elseif($transaction->transaction_type == 'writeoff')
                          <span class="badge badge-danger">Write-off</span>
                        @else
                          <span class="badge badge-secondary">Adjustment</span>
                        @endif
                      </td>
                      <td>{{ $transaction->description ?? '-' }}</td>
                      <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                      <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                      <td class="text-right">
                        <span class="badge badge-{{ $balance > 0 ? 'success' : ($balance < 0 ? 'danger' : 'secondary') }}">
                          {{ number_format($balance, 2) }}
                        </span>
                      </td>
                      <td>
                        @if($transaction->attachment)
                          <a href="{{ asset('storage/' . $transaction->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-download"></i>
                          </a>
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('asset.edit', $transaction->asset_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('asset.destroy', $transaction->asset_id) }}" method="POST" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</button>
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="9" class="text-center">No transactions found</td>
                    </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  @if($transactions->count())
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                      <td colspan="4" class="text-right">Total:</td>
                      <td class="text-right">{{ number_format($transactions->sum('debit'), 2) }}</td>
                      <td class="text-right">{{ number_format($transactions->sum('credit'), 2) }}</td>
                      <td class="text-right">
                        <span class="badge badge-{{ $netValue > 0 ? 'success' : ($netValue < 0 ? 'danger' : 'secondary') }}">
                          {{ number_format($netValue, 2) }}
                        </span>
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


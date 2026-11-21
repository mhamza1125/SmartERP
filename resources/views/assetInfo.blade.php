{{-- Ledger of one asset / liability name ($register from AssetController / LiabilityController) --}}
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ $register['title'] }} Ledger: {{ $assetName }}</h4>
            <div class="card-header-action">
              @can('create', $register['model'])<a href="{{ route($register['route'] . '.add', ['name' => $assetName]) }}" class="btn btn-primary">Add Entry</a>@endcan
              <a href="{{ route($register['route']) }}" class="btn btn-secondary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <div class="ptc-facts ptc-facts--4 mb-4">
              <div><span class="ptc-fact-label">{{ $register['title'] }}</span><span class="ptc-fact-value">{{ $assetName }}</span></div>
              <div><span class="ptc-fact-label">{{ $register['increase'] }}</span><span class="ptc-fact-value">{{ number_format($transactions->sum('debit'), 2) }}</span></div>
              <div><span class="ptc-fact-label">{{ $register['decrease'] }}</span><span class="ptc-fact-value">{{ number_format($transactions->sum('credit'), 2) }}</span></div>
              <div><span class="ptc-fact-label">{{ $register['balance'] }}</span><span class="ptc-fact-value"><strong>{{ number_format($netValue, 2) }}</strong></span></div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Cash / Bank</th>
                    <th class="num">{{ $register['increase'] }}</th>
                    <th class="num">{{ $register['decrease'] }}</th>
                    <th class="num">{{ $register['balance'] }}</th>
                    <th>Attachment</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php $balance = 0; @endphp
                  @foreach($transactions as $transaction)
                    @php
                      $debit = (float) ($transaction->debit ?? 0);
                      $credit = (float) ($transaction->credit ?? 0);
                      $balance += $debit - $credit;
                      $type = $types[$transaction->transaction_type] ?? null;
                    @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ formatDate($transaction->transaction_date) }}</td>
                      <td><span class="badge badge-{{ ($type['sign'] ?? 1) > 0 ? 'success' : 'warning' }}">{{ $type['short'] ?? ucfirst($transaction->transaction_type) }}</span></td>
                      <td>{{ $transaction->description ?? '-' }}</td>
                      <td class="text-nowrap">
                        @if($transaction->transaction_id)
                          @php $account = $transaction->bank_id ? trim(($transaction->bank_name ?? 'Bank') . ' - ' . $transaction->account_title, ' -') : 'Cash'; @endphp
                          {{ ($type['money'] ?? null) === 'in' ? 'Received into' : 'Paid from' }} {{ $account }}
                          <small class="d-block text-muted">Transaction #{{ $transaction->transaction_id }}</small>
                        @else
                          <span class="text-muted">No money moved</span>
                        @endif
                      </td>
                      <td class="num">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                      <td class="num">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                      <td class="num">{{ number_format($balance, 2) }}</td>
                      <td>
                        @if($transaction->attachment)
                          <a href="{{ asset('storage/' . $transaction->attachment) }}" target="_blank" class="btn btn-sm btn-info" title="Open attachment"><i class="fas fa-download"></i></a>
                        @else
                          -
                        @endif
                      </td>
                      <td class="text-nowrap">
                        @can('edit', $register['model'])<a href="{{ route($register['route'] . '.edit', $transaction->asset_id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
                        @can('delete', $register['model'])@include('deleteButton', ['url' => route($register['route'] . '.destroy', $transaction->asset_id), 'check' => route($register['route'] . '.deleteCheck', $transaction->asset_id), 'label' => $register['title'] . ' entry #' . $transaction->asset_id])@endcan
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="5" class="text-right">Total</th>
                    <th class="num">{{ number_format($transactions->sum('debit'), 2) }}</th>
                    <th class="num">{{ number_format($transactions->sum('credit'), 2) }}</th>
                    <th class="num">{{ number_format($netValue, 2) }}</th>
                    <th colspan="2"></th>
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

{{-- Assets / Liabilities list: one row per name ($register from AssetController / LiabilityController) --}}
@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ $register['plural'] }}</h4>
            <div class="card-header-action">
              @can('show', $register['model'])<a href="{{ route($register['route'] . '.print') }}" class="btn btn-info" target="_blank"><i class="fas fa-file-alt"></i> Print Ledger</a>@endcan
              @can('create', $register['model'])<a href="{{ route($register['route'] . '.add') }}" class="btn btn-primary">Add {{ $register['title'] }}</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <p class="text-muted small">
              @if($register['kind'] === 'asset')
                Value = additions (purchases, improvements, increases) minus reductions (sales, depreciation, write-offs, decreases).
              @else
                Outstanding = new liabilities and charges minus payments, waivers and decreases.
              @endif
              Entries marked as affecting cash / bank are posted to the Cash Book / Bank Ledger.
            </p>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>{{ $register['title'] }}</th>
                    <th class="num">{{ $register['increase'] }}</th>
                    <th class="num">{{ $register['decrease'] }}</th>
                    <th class="num">{{ $register['balance'] }}</th>
                    <th class="num" title="Received into cash / bank by these entries">Money In</th>
                    <th class="num" title="Paid from cash / bank by these entries">Money Out</th>
                    <th class="num">Entries</th>
                    <th>Last Entry</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($assets as $item)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $item->asset_name }}</td>
                      <td class="num">{{ number_format($item->total_debit, 2) }}</td>
                      <td class="num">{{ number_format($item->total_credit, 2) }}</td>
                      <td class="num"><strong>{{ number_format($item->net_value, 2) }}</strong></td>
                      <td class="num">{{ $item->money_in ? number_format($item->money_in, 2) : '-' }}</td>
                      <td class="num">{{ $item->money_out ? number_format($item->money_out, 2) : '-' }}</td>
                      <td class="num">{{ $item->entries }}</td>
                      <td>{{ formatDate($item->last_transaction_date) }}</td>
                      <td class="text-nowrap">
                        @can('show', $register['model'])<a href="{{ route($register['route'] . '.show', ['name' => $item->asset_name]) }}" class="btn btn-info btn-sm">Ledger</a>@endcan
                        @can('create', $register['model'])<a href="{{ route($register['route'] . '.add', ['name' => $item->asset_name]) }}" class="btn btn-primary btn-sm">Add Entry</a>@endcan
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                @if($assets->count())
                  <tfoot>
                    <tr>
                      <th colspan="2" class="text-right">Total</th>
                      <th class="num">{{ number_format($assets->sum('total_debit'), 2) }}</th>
                      <th class="num">{{ number_format($assets->sum('total_credit'), 2) }}</th>
                      <th class="num">{{ number_format($assets->sum('net_value'), 2) }}</th>
                      <th class="num">{{ number_format($assets->sum('money_in'), 2) }}</th>
                      <th class="num">{{ number_format($assets->sum('money_out'), 2) }}</th>
                      <th class="num">{{ $assets->sum('entries') }}</th>
                      <th colspan="2"></th>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

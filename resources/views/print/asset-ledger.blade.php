@extends('print.layout')

{{--
    Asset / liability ledger print (AssetEntryController::printLedger).
    $ledgers: entries grouped by name (one group when $assetName is set); each group has its own
    running balance. $register: labels of the register (asset / liability).
--}}
@php
    $single = $assetName !== '';
    $all = $ledgers->flatten(1);
    $totalIncrease = (float) $all->sum('debit');
    $totalDecrease = (float) $all->sum('credit');
    $moneyAccount = fn ($row) => $row->bank_id ? trim(($row->bank_name ?? 'Bank').' - '.$row->account_title, ' -') : 'Cash';
@endphp

@section('title', str_replace(' ', '_', $register['title'].'_Ledger_'.($single ? $assetName : 'All')))
@section('doc_title', $single ? $register['title'].' Ledger' : $register['plural'].' Ledger')

@section('content')
<div class="meta-band meta-band--grid">
    <div class="meta-field">
        <span class="meta-label">{{ $register['title'] }}</span>
        <span class="meta-value">{{ $single ? $assetName : 'All '.$register['plural'].' ('.$ledgers->count().')' }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Entries</span>
        <span class="meta-value">{{ $all->count() }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">Printed On</span>
        <span class="meta-value">{{ formatDate(now()) }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $register['increase'] }}</span>
        <span class="meta-value amount">{{ number_format($totalIncrease, 2) }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $register['decrease'] }}</span>
        <span class="meta-value amount">{{ number_format($totalDecrease, 2) }}</span>
    </div>
    <div class="meta-field">
        <span class="meta-label">{{ $register['balance'] }}</span>
        <span class="meta-value amount"><strong>{{ number_format($totalIncrease - $totalDecrease, 2) }}</strong></span>
    </div>
</div>

@forelse($ledgers as $name => $entries)
    @php $balance = 0; @endphp
    <div class="info-section">
        @unless($single)<div class="table-title">{{ $name }}</div>@endunless
        <table class="print-table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Entry No</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Cash / Bank</th>
                    <th class="num">{{ $register['increase'] }}</th>
                    <th class="num">{{ $register['decrease'] }}</th>
                    <th class="num">{{ $register['balance'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $row)
                    @php
                        $debit = (float) ($row->debit ?? 0);
                        $credit = (float) ($row->credit ?? 0);
                        $balance += $debit - $credit;
                        $type = $types[$row->transaction_type] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ formatDate($row->transaction_date) }}</td>
                        <td>{{ strtoupper(substr($register['kind'], 0, 3)) }}-{{ str_pad($row->asset_id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $type['short'] ?? ucfirst($row->transaction_type) }}</td>
                        <td>{{ $row->description ?: '-' }}</td>
                        <td>
                            @if($row->transaction_id)
                                {{ ($type['money'] ?? null) === 'in' ? 'Into' : 'From' }} {{ $moneyAccount($row) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="num">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                        <td class="num">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                        <td class="num">{{ number_format($balance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>{{ $single ? 'Total' : 'Total - '.$name }}</strong></td>
                    <td class="num"><strong>{{ number_format($entries->sum('debit'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($entries->sum('credit'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($balance, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@empty
    <p>No entries.</p>
@endforelse

@if(! $single && $ledgers->count() > 1)
    <div class="totals-section avoid-break">
        <div class="total-row grand-total">
            <span>Total {{ $register['balance'] }} ({{ $register['plural'] }})</span>
            <span class="amount">{{ number_format($totalIncrease - $totalDecrease, 2) }}</span>
        </div>
    </div>
@endif
@endsection

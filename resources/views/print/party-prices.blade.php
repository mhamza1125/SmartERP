@extends('print.layout')

{{--
  Price list of one customer (selling prices) or vendor (buying prices): PartyPriceRepository::printData.
  Params: $partyType ('vendor' | 'customer'), $party, $prices
--}}
@php
  $isVendor = $partyType === 'vendor';
  $money = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
@endphp

@section('title', ($isVendor ? 'Vendor' : 'Customer').'_Price_List_'.($party->party_no ?? '').'_'.date('d-m-Y'))
@section('doc_title', $isVendor ? 'Vendor Price List (Buying)' : 'Customer Price List (Selling)')
@section('doc_no', $party->party_no ?? '')

@section('content')
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">{{ $isVendor ? 'Vendor' : 'Customer' }} No</span>
            <span class="meta-value">{{ $party->party_no ?? '-' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Name</span>
            <span class="meta-value">{{ $party->party_name ?: '-' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">{{ $isVendor ? 'City' : 'Country' }}</span>
            <span class="meta-value">{{ $party->place ?? '-' }}</span>
        </div>
    </div>
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Phone</span>
            <span class="meta-value">{{ $party->phone ?? '-' }}</span>
        </div>
        @if(! $isVendor)
            <div class="meta-field">
                <span class="meta-label">Currency</span>
                <span class="meta-value">{{ $party->currency_name ?? '-' }}</span>
            </div>
        @endif
        <div class="meta-field">
            <span class="meta-label">Items</span>
            <span class="meta-value">{{ $prices->count() }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Printed On</span>
            <span class="meta-value">{{ formatDate(now()) }}</span>
        </div>
    </div>
</div>

<div class="info-section">
    <h3>{{ $isVendor ? 'Buying Prices' : 'Selling Prices' }}</h3>
    @if($prices->count())
        <table class="print-table">
            <thead>
                <tr>
                    <th class="text-center">Sr.</th>
                    <th class="text-left">Item</th>
                    <th class="text-left">{{ $isVendor ? 'Size / Unit' : 'Size' }}</th>
                    <th class="text-left">Stage</th>
                    <th class="num">Price{{ ! $isVendor && $party->currency_name ? ' ('.$party->currency_name.')' : '' }}</th>
                    <th class="text-left">Since</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prices as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $row->item_name }}</td>
                        <td>{{ $row->item_type === 'material' ? ($row->unit_name ?? '-') : ($row->size_name ?? '-') }}</td>
                        <td>{{ $row->item_type === 'material' ? '-' : ($row->stage_name ?? '-') }}</td>
                        <td class="num">{{ $money($row->price) }}</td>
                        <td>{{ formatDate($row->updated_at) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No prices saved for this {{ $isVendor ? 'vendor' : 'customer' }}.</p>
    @endif
</div>
@endsection

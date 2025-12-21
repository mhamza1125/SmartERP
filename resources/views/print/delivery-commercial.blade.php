@extends('print.layout')

@section('title', 'Commercial_Invoice_' . ($delivery['customer_no'] ?? 'N/A') . '_' . date('Y-m-d'))

@section('content')
<div class="document-title">Commercial Invoice</div>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $delivery['fname'] ?? 'N/A' }} {{ $delivery['lname'] ?? '' }}</span>
        </div>
        @if(isset($delivery['email']) && !empty($delivery['email']))
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $delivery['email'] }}</span>
        </div>
        @endif
        @if(isset($delivery['address']) && !empty($delivery['address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $delivery['address'] }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value">{{ date('Y-m-d') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Delivery No:</span>
            <span class="info-value">{{ $delivery['customer_no'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Company Information from Company Table --}}
@if(isset($company))
<div class="company-info avoid-break">
    <h3>Company Information</h3>
    <table class="print-table">
        <tbody>
            @if(isset($company->ntn) && !empty($company->ntn))
            <tr>
                <td><strong>NTN:</strong></td>
                <td>{{ $company->ntn }}</td>
            </tr>
            @endif
            @if(isset($company->rex_no) && !empty($company->rex_no))
            <tr>
                <td><strong>REX No:</strong></td>
                <td>{{ $company->rex_no }}</td>
            </tr>
            @endif
            @if(isset($delivery['fi_no']) && !empty($delivery['fi_no']))
            <tr>
                <td><strong>FI No:</strong></td>
                <td>{{ $delivery['fi_no'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Delivered Items Table --}}
@if(isset($deliveryItem) && $deliveryItem->count() > 0)
<div class="avoid-break">
    <h3>Delivered Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 10%">Article No</th>
                <th style="width: 8%">HS Code</th>
                <th style="width: 24%">Product Name</th>
                <th style="width: 10%">Size</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 13%">Unit Price</th>
                <th style="width: 13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryItem->count())
                @php $product_id = 0; @endphp
                @foreach($deliveryItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="3"></td>
                    @else
                        <td class="text-center">{{ $item->article_no }}</td>
                        <td class="text-center">{{ $item->hs_code ?? '-' }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        @php $product_id = $item->product_id; @endphp
                    @endif
                    <td class="text-center">{{ $item->sname ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price2 ?? 0, 2) }} {{ $item->cname ?? 'PKR' }}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price2 ?? 0), 2) }} {{ $item->cname ?? 'PKR' }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

{{-- Invoice Totals --}}
@php
$totalOriginal = $deliveryItem->sum(function($item) {
    return $item->quantity * ($item->price2 ?? 0);
});
$firstItem = $deliveryItem->first();
$currencyName = $firstItem->cname ?? 'PKR';
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>
@endif

{{-- Statement of Origin --}}
@if(isset($company) && isset($company->statement_of_origin) && !empty($company->statement_of_origin))
<div class="statement-of-origin avoid-break">
    <h3>Statement of Origin</h3>
    <p>{{ $company->statement_of_origin }}</p>
</div>
@endif

@endsection


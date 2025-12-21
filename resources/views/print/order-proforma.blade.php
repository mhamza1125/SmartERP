@extends('print.layout')

@section('title', 'Proforma_Invoice_' . ($order['order_no'] ?? 'N/A') . '_' . ($order['order_date'] ?? date('Y-m-d')))

@section('content')
<div class="document-title">Proforma Invoice</div>

{{-- Customer Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $order['fname'] ?? 'N/A' }} {{ $order['lname'] ?? '' }}</span>
        </div>
        @if(isset($order['email']) && !empty($order['email']))
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $order['email'] }}</span>
        </div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value">{{ $order['order_date'] ?? 'N/A' }}</span>
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
        </tbody>
    </table>
</div>
@endif

{{-- Order Items Table --}}
@if(isset($orderItem) && $orderItem->count() > 0)
<div class="avoid-break">
    <h3>Invoice Items</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 9%">Article No</th>
                <th style="width: 8%">HS Code</th>
                <th style="width: 20%">Product Name</th>
                <th style="width: 10%">Size</th>
                <th style="width: 10%">Quantity</th>
                <th style="width: 12%">Unit Price</th>
                <th style="width: 16%">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($orderItem->count())
                @php $product_id = 0; @endphp
                @foreach($orderItem as $item)
                <tr>
                    <td class="text-center">{{ $loop->index + 1 }}</td>
                    @if($item->product_id == $product_id)
                        <td colspan="4"></td>
                    @else
                        <td class="text-center">{{ $item->article_no }}</td>
                        <td class="text-center">{{ $item->hs_code ?? '-' }}</td>
                        <td class="text-center">{{ $item->pname }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        @php $product_id = $item->product_id; @endphp
                    @endif
                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                    <td class="text-right amount">{{ number_format($item->price2 ?? 0, 2) }} {{ $item->cname }}</td>
                    <td class="text-right amount">{{ number_format($item->quantity * ($item->price2 ?? 0), 2) }} {{ $item->cname }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- Invoice Totals --}}
@php
$totalOriginal = $orderItem->sum(function($item) {
    return $item->quantity * ($item->price2 ?? 0);
});
$firstItem = $orderItem->first();
$currencyName = $firstItem->cname ?? 'PKR';
@endphp
<div class="totals-section avoid-break">
    <div class="total-row grand-total">
        <span>Grand Total:</span>
        <span class="amount">{{ number_format($totalOriginal, 2) }} {{ $currencyName }}</span>
    </div>
</div>

{{-- Bank Account Details --}}
@if(isset($bankDetails) && !empty($bankDetails))
<div class="info-section avoid-break">
    <h3>Bank Account Details</h3>
    <table class="print-table">
        <tbody>
            <tr>
                <td><strong>Account Title:</strong></td>
                <td>{{ $bankDetails['account_title'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Account Number:</strong></td>
                <td>{{ $bankDetails['account'] ?? 'N/A' }}</td>
            </tr>
            @if(isset($bankDetails['iban']) && !empty($bankDetails['iban']))
            <tr>
                <td><strong>IBAN:</strong></td>
                <td>{{ $bankDetails['iban'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['swift_code']) && !empty($bankDetails['swift_code']))
            <tr>
                <td><strong>SWIFT Code:</strong></td>
                <td>{{ $bankDetails['swift_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['branch_code']) && !empty($bankDetails['branch_code']))
            <tr>
                <td><strong>Branch Code:</strong></td>
                <td>{{ $bankDetails['branch_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['address']) && !empty($bankDetails['address']))
            <tr>
                <td><strong>Bank Address:</strong></td>
                <td>{{ $bankDetails['address'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
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


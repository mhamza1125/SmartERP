@extends('print.layout')

@section('title', 'Product_Cost_' . ($product['article_no'] ?? 'N/A') . '_' . ($product['name'] ?? 'N/A'))

@section('content')
<div class="document-title">Product Cost Information</div>

{{-- Product Header Information --}}
<div class="document-info">
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Article No:</span>
            <span class="info-value">{{ $product['article_no'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Product Name:</span>
            <span class="info-value">{{ $product['name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Product Type:</span>
            <span class="info-value">{{ $product['product_type_name'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Category:</span>
            <span class="info-value">{{ $product['category_name'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Unit:</span>
            <span class="info-value">{{ $product['unit_name'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Product Cost Table --}}
@if($productCost && $productCost->count() > 0)
<div class="document-section">
    <h3>Cost Breakdown</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 5%">Sr.</th>
                <th style="width: 40%">Employee / Vendor</th>
                <th style="width: 35%">Cost Head</th>
                <th style="width: 20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalCost = 0; @endphp
            @foreach($productCost as $item)
            @php $totalCost += $item->amount ?? 0; @endphp
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td>
                    @if($item->employee_no)
                        {{ $item->employee_no }}{{ $item->name ? ' - ' . $item->name : '' }}
                    @elseif($item->vendor_no)
                        {{ $item->vendor_no }}{{ $item->name ? ' - ' . $item->name : '' }}
                    @else
                        General Cost
                    @endif
                </td>
                <td>{{ $item->hname ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>Total Cost:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalCost, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
@else
<div class="alert alert-warning">
    <h5>No Cost Data</h5>
    <p>No cost information has been recorded for this product.</p>
</div>
@endif

<style>
    .document-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
        text-decoration: underline;
    }

    .document-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-section {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 4px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .info-label {
        font-weight: bold;
        min-width: 150px;
    }

    .info-value {
        flex: 1;
        text-align: right;
    }

    .document-section {
        margin-bottom: 30px;
    }

    .document-section h3 {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        border-bottom: 2px solid #333;
        padding-bottom: 5px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .print-table thead {
        background-color: #f5f5f5;
    }

    .print-table th,
    .print-table td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 12px;
    }

    .print-table th {
        font-weight: bold;
        text-align: left;
    }

    .print-table tfoot {
        background-color: #f9f9f9;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .total-row {
        border-top: 2px solid #333;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }
</style>
@endsection


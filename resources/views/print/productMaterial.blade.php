@extends('print.layout')

@section('title', 'Product_Material_' . ($productType['article_no'] ?? 'N/A') . '_' . date('d-m-Y'))
@section('doc_title', 'Product Materials')
@section('doc_no', $productType['article_no'] ?? '')

@section('content')
{{-- Product Information --}}
<div class="meta-band meta-band--grid">
    <div class="meta-group">
        <div class="meta-field">
            <span class="meta-label">Article No</span>
            <span class="meta-value">{{ $productType['article_no'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Product Name</span>
            <span class="meta-value">{{ $productType['name'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Size</span>
            <span class="meta-value">{{ $productType['hname'] ?? 'N/A' }}</span>
        </div>
        <div class="meta-field">
            <span class="meta-label">Unit</span>
            <span class="meta-value">{{ $productType['uname'] ?? 'N/A' }}</span>
        </div>
    </div>
</div>

{{-- Product Packing Information --}}
@php $packingItem = $productMaterial->firstWhere('material_type_id', '61'); @endphp
@if($packingItem)
<div class="info-section avoid-break">
    <h3>Product Packing</h3>
    <div class="meta-band meta-band--grid">
        <div class="meta-group">
            <div class="meta-field">
                <span class="meta-label">Box No</span>
                <span class="meta-value">{{ $packingItem->material_no ?? 'N/A' }}</span>
            </div>
            <div class="meta-field">
                <span class="meta-label">Box Name</span>
                <span class="meta-value">{{ $packingItem->name ?? 'N/A' }}</span>
            </div>
            <div class="meta-field">
                <span class="meta-label">Quantity in Box</span>
                <span class="meta-value">{{ $packingItem->quantity ? number_format(1/$packingItem->quantity, 2) : 'N/A' }} {{ $productType['uname'] ?? '' }}</span>
            </div>
        </div>
    </div>
    
    @if(isset($packingItem->description) && !empty($packingItem->description))
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9; margin-top: 10px;">
        <strong>Details:</strong><br>
        {!! nl2br(e(strip_tags($packingItem->description))) !!}
    </div>
    @endif
</div>
@endif

{{-- Product Materials --}}
@if($productMaterial->count() > 0)
<div class="items-section avoid-break">
    <h3>Product Materials</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Material No</th>
                <th>Material</th>
                <th>QTY</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; @endphp
            @foreach($productMaterial as $item)
                @unless($item->material_type_id == '61')
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $item->material_no ?? 'N/A' }}</td>
                    <td>{{ $item->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ isset($item->quantity) ? number_format($item->quantity, 2) : 'N/A' }}</td>
                    <td>{{ $item->hname ?? 'N/A' }}</td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="info-section">
    <p style="text-align: center; color: #666;">No materials defined for this product.</p>
</div>
@endif

{{-- Summary --}}
<div class="totals-section avoid-break">
    <div class="total-row">
        <span>Total Materials:</span>
        <span>{{ $productMaterial->where('material_type_id', '!=', '61')->count() }}</span>
    </div>
    @if($packingItem)
    <div class="total-row">
        <span>Packing Type:</span>
        <span>{{ $packingItem->name ?? 'N/A' }}</span>
    </div>
    @endif
</div>

@endsection


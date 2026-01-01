@extends('print.layout')

@section('title', 'Product_Info_' . ($product['article_no'] ?? 'N/A') . '_' . date('d-m-Y'))

@section('content')
<div class="document-title">Product Information</div>

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
            <span class="info-label">Unit:</span>
            <span class="info-value">{{ $product['hname'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Category:</span>
            <span class="info-value">{{ $product['cname'] ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Current Status:</span>
            <span class="info-value status-badge">
                @if($product['product_status']) 
                    <span class="badge badge-success">Active</span> 
                @else 
                    <span class="badge badge-danger">Inactive</span> 
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Sizes Available:</span>
            <span class="info-value">
                @if($size->count())
                    @foreach($size as $item) 
                        {{ $item->name }}@if(!$loop->last), @endif
                    @endforeach
                @else
                    N/A
                @endif
            </span>
        </div>
    </div>
</div>

{{-- Product Description --}}
@if(isset($product['description']) && !empty($product['description']))
<div class="info-section avoid-break">
    <h3>Product Description</h3>
    <div style="border: 1px solid #333; padding: 10px; background-color: #f9f9f9;">
        {!! nl2br(e(strip_tags($product['description']))) !!}
    </div>
</div>
@endif

{{-- Product Types/Variations --}}
@if(isset($totalMaterial) && count($totalMaterial) > 0)
<div class="avoid-break">
    <h3>Product Variations</h3>
    @foreach($totalMaterial as $index => $variation)
        <div class="variation-section" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
            <h4>{{ $variation['article_no'] ?? 'N/A' }} - {{ $variation['pname'] ?? 'N/A' }} (Size: {{ $variation['name'] ?? 'N/A' }})</h4>
            
            {{-- Box Information --}}
            @php 
                $boxInfo = $getMaterial->where('material_type_id', '61')->where('product_type_id', $variation['product_type_id'])->first(); 
            @endphp
            @if($boxInfo)
                <div class="box-info" style="margin-bottom: 15px;">
                    <strong>Box Information:</strong><br>
                    Box No: {{ $boxInfo->material_no }}<br>
                    Box Name: {{ $boxInfo->name }}<br>
                    Quantity in Box: {{ 1/$boxInfo->quantity }} {{ $product['hname'] }}
                </div>
            @endif
            
            {{-- Material Costing Table --}}
            <table class="print-table" style="width: 100%; margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Material No</th>
                        <th>Material Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Units</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $mprice = 0; 
                        $loopIndex = 1;
                    @endphp
                    @foreach($getMaterial as $item)
                        @if(($variation['product_type_id'] ?? null) === $item->product_type_id)
                            @unless($item->material_type_id == '61')
                                <tr>
                                    <td>{{ $loopIndex++ }}</td>
                                    <td>{{ $item->material_no }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->cprice * $item->quantity, 2) }}</td>
                                    <td>{{ $item->hname }}</td>
                                </tr>
                                @php $mprice += $item->cprice * $item->quantity; @endphp
                            @endunless
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td colspan="4" class="text-right">Total Material Cost:</td>
                        <td>{{ number_format($mprice, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- Product Stages --}}
@if(isset($stage) && $stage->count() > 0)
<div class="avoid-break">
    <h3>Production Stages</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Stage Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stage as $item)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>{{ $item->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="avoid-break">
    <h3>Production Stages</h3>
    <p>No Stages</p>
</div>
@endif

{{-- Opening Stock --}}
@if(isset($openingStock) && $openingStock->count() > 0)
<div class="avoid-break">
    <h3>Opening Stock</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Size</th>
                <th>Stage</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($openingStock as $item)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>{{ $item->size_name }}</td>
                <td>{{ $item->stage_name }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Product Costing --}}
@if(isset($pcost) && $pcost->count() > 0)
<div class="avoid-break">
    <h3>Product Costing / Wages</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Employee / Vendor</th>
                <th>Cost Head</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pcost as $item)
            <tr>
                <td>{{ $loop->index + 1 }}</td>
                <td>{{ $item->employee_no ?? $item->vendor_no }}{{ $item->name ? ' - ' . $item->name : ' (General Cost)' }}</td>
                <td>{{ $item->hname ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="3" class="text-right">Total Wages:</td>
                <td class="text-right">{{ number_format($pWages ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection

# Product Travel Card (PTC) - Streamlined Implementation Guide

## 🎯 Overview

This streamlined PTC implementation leverages the existing database structure and issuance system to generate Product Travel Cards without creating new tables. The PTC is essentially a comprehensive report that consolidates existing data from multiple tables to show a product's complete manufacturing journey.

## 🗄️ Database Tables Used (No New Tables Required)

### **Core Tables**
- **`orders`** - Order information and customer details
- **`order_items`** - Products ordered with quantities and stages
- **`product_types`** - Product specifications (size, color)
- **`products`** - Product details (article_no, name, stage_ids)
- **`heads`** - Manufacturing stages and units
- **`materials`** - Raw materials information
- **`stocks`** - Material issuance records
- **`stock_items`** - Detailed material issuance per stage
- **`igroups`** - Issuance groups (batch issuances)
- **`igroup_items`** - Individual items in issuance groups

### **Key Relationships**
```
orders → order_items → product_types → products
stocks → stock_items → materials + stages (heads)
igroups → igroup_items → materials + stages (heads)
```

## 🎮 Controller Implementation

### **PTCController.php**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PTCController extends Controller
{
    /**
     * Show PTC for a specific order and product
     */
    public function show($order_id, $product_type_id)
    {
        $ptcData = $this->getPTCData($order_id, $product_type_id);
        
        return view('ptc.show', compact('ptcData'));
    }
    
    /**
     * Generate PDF for PTC
     */
    public function generatePDF($order_id, $product_type_id)
    {
        $ptcData = $this->getPTCData($order_id, $product_type_id);
        
        $pdf = PDF::loadView('ptc.pdf', compact('ptcData'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "PTC-{$ptcData['order']['order_no']}-{$ptcData['product']['article_no']}.pdf";
        
        return $pdf->download($filename);
    }
    
    /**
     * Get all PTC data from existing tables
     */
    private function getPTCData($order_id, $product_type_id)
    {
        // Get order information
        $order = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.customer_id')
            ->select('o.*', 'c.fname', 'c.lname', 'c.customer_no')
            ->where('o.order_id', $order_id)
            ->first();
            
        // Get product information
        $product = DB::table('product_types as pt')
            ->join('products as p', 'pt.product_id', '=', 'p.product_id')
            ->join('heads as size', 'pt.size_id', '=', 'size.head_id')
            ->leftJoin('heads as color', 'pt.color_id', '=', 'color.head_id')
            ->select('p.article_no', 'p.name as product_name', 'p.stage_ids',
                    'size.name as size_name', 'color.name as color_name')
            ->where('pt.product_type_id', $product_type_id)
            ->first();
            
        // Get order item details
        $orderItem = DB::table('order_items')
            ->where('order_id', $order_id)
            ->where('product_type_id', $product_type_id)
            ->first();
            
        // Get manufacturing stages
        $stageIds = explode(',', $product->stage_ids);
        $stages = DB::table('heads')
            ->whereIn('head_id', $stageIds)
            ->orderByRaw("FIELD(head_id, " . implode(',', $stageIds) . ")")
            ->get();
            
        // Get material issuances per stage
        $materialIssuances = $this->getMaterialIssuances($order_id, $product_type_id);
        
        return [
            'order' => $order,
            'product' => $product,
            'orderItem' => $orderItem,
            'stages' => $stages,
            'materialIssuances' => $materialIssuances,
            'ptc_number' => $this->generatePTCNumber($order_id, $product_type_id)
        ];
    }
    
    /**
     * Get material issuances from stocks and igroups
     */
    private function getMaterialIssuances($order_id, $product_type_id)
    {
        // Get from stock_items (individual issuances)
        $stockIssuances = DB::table('stock_items as si')
            ->join('stocks as s', 'si.stock_id', '=', 's.stock_id')
            ->join('materials as m', 'si.material_id', '=', 'm.material_id')
            ->join('heads as stage', 'si.stage_id', '=', 'stage.head_id')
            ->join('heads as unit', 'm.unit_id', '=', 'unit.head_id')
            ->select('si.*', 'm.name as material_name', 'm.material_no',
                    'stage.name as stage_name', 'unit.name as unit_name',
                    's.stock_date', 's.stock_no', 'stock' as source_type)
            ->where('s.order_id', $order_id)
            ->where('si.product_type_id', $product_type_id)
            ->get();
            
        // Get from igroup_items (batch issuances)
        $igroupIssuances = DB::table('igroup_items as ii')
            ->join('igroups as ig', 'ii.igroup_id', '=', 'ig.igroup_id')
            ->join('materials as m', 'ii.material_id', '=', 'm.material_id')
            ->join('heads as stage', 'ii.stage_id', '=', 'stage.head_id')
            ->join('heads as unit', 'm.unit_id', '=', 'unit.head_id')
            ->select('ii.*', 'm.name as material_name', 'm.material_no',
                    'stage.name as stage_name', 'unit.name as unit_name',
                    'ig.igroup_date as stock_date', 'ig.igroup_no as stock_no', 
                    'igroup' as source_type)
            ->where('ig.order_id', $order_id)
            ->where('ii.product_type_id', $product_type_id)
            ->get();
            
        // Combine and group by stage
        $allIssuances = $stockIssuances->concat($igroupIssuances);
        
        return $allIssuances->groupBy('stage_id');
    }
    
    /**
     * Generate PTC number
     */
    private function generatePTCNumber($order_id, $product_type_id)
    {
        return "PTC-" . date('Y') . "-" . str_pad($order_id, 4, '0', STR_PAD_LEFT) . 
               "-" . str_pad($product_type_id, 3, '0', STR_PAD_LEFT);
    }
}
```

## 🖼️ View Files Required

### **1. resources/views/ptc/show.blade.php** (Web View)
```php
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4>Product Travel Card - {{ $ptcData['ptc_number'] }}</h4>
                    <a href="{{ route('ptc.pdf', [$ptcData['order']->order_id, $ptcData['orderItem']->product_type_id]) }}" 
                       class="btn btn-primary">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                </div>
                <div class="card-body">
                    @include('ptc.content', ['ptcData' => $ptcData])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### **2. resources/views/ptc/pdf.blade.php** (PDF Template)
```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Travel Card - {{ $ptcData['ptc_number'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 5px; border: 1px solid #ddd; }
        .stage-section { margin-bottom: 20px; }
        .stage-header { background-color: #f5f5f5; font-weight: bold; }
        .material-table { width: 100%; border-collapse: collapse; }
        .material-table th, .material-table td { 
            padding: 4px; border: 1px solid #ddd; text-align: left; 
        }
        .material-table th { background-color: #f9f9f9; }
    </style>
</head>
<body>
    @include('ptc.content', ['ptcData' => $ptcData])
</body>
</html>
```

### **3. resources/views/ptc/content.blade.php** (Shared Content)
```php
<div class="ptc-content">
    <!-- Header -->
    <div class="header">
        <h2>PRODUCT TRAVEL CARD</h2>
        <h3>{{ $ptcData['ptc_number'] }}</h3>
    </div>

    <!-- Order Information -->
    <table class="info-table">
        <tr>
            <td><strong>Order No:</strong></td>
            <td>{{ $ptcData['order']->order_no }}</td>
            <td><strong>Job No:</strong></td>
            <td>{{ $ptcData['order']->job_no }}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong></td>
            <td>{{ $ptcData['order']->customer_no }} - {{ $ptcData['order']->fname }} {{ $ptcData['order']->lname }}</td>
            <td><strong>Order Date:</strong></td>
            <td>{{ $ptcData['order']->order_date }}</td>
        </tr>
    </table>

    <!-- Product Information -->
    <table class="info-table">
        <tr>
            <td><strong>Article No:</strong></td>
            <td>{{ $ptcData['product']->article_no }}</td>
            <td><strong>Product Name:</strong></td>
            <td>{{ $ptcData['product']->product_name }}</td>
        </tr>
        <tr>
            <td><strong>Size:</strong></td>
            <td>{{ $ptcData['product']->size_name }}</td>
            <td><strong>Color:</strong></td>
            <td>{{ $ptcData['product']->color_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Quantity:</strong></td>
            <td>{{ number_format($ptcData['orderItem']->quantity) }}</td>
            <td><strong>Price:</strong></td>
            <td>{{ number_format($ptcData['orderItem']->price ?? 0) }}</td>
        </tr>
    </table>

    <!-- Manufacturing Stages and Material Issuances -->
    @foreach($ptcData['stages'] as $stage)
        <div class="stage-section">
            <h4 class="stage-header">Stage: {{ $stage->name }}</h4>

            @if(isset($ptcData['materialIssuances'][$stage->head_id]))
                <table class="material-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Issue No</th>
                            <th>Material</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ptcData['materialIssuances'][$stage->head_id] as $issuance)
                            <tr>
                                <td>{{ $issuance->stock_date }}</td>
                                <td>{{ $issuance->stock_no }}</td>
                                <td>{{ $issuance->material_no }} - {{ $issuance->material_name }}</td>
                                <td>{{ number_format($issuance->quantity, 2) }}</td>
                                <td>{{ $issuance->unit_name }}</td>
                                <td>{{ ucfirst($issuance->source_type) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p><em>No materials issued for this stage yet.</em></p>
            @endif
        </div>
    @endforeach
</div>
```

## 🛣️ Routes Required

### **routes/web.php**
```php
// PTC Routes
Route::prefix('ptc')->name('ptc.')->group(function () {
    Route::get('/{order_id}/{product_type_id}', [PTCController::class, 'show'])->name('show');
    Route::get('/{order_id}/{product_type_id}/pdf', [PTCController::class, 'generatePDF'])->name('pdf');
});
```

## 📄 PDF Generation Setup

### **Installation**
```bash
composer require barryvdh/laravel-dompdf
```

### **Configuration (config/app.php)**
```php
'providers' => [
    Barryvdh\DomPDF\ServiceProvider::class,
],

'aliases' => [
    'PDF' => Barryvdh\DomPDF\Facade::class,
],
```

## 🔄 Complete Data Flow

### **1. Data Sources**
- **Order Info**: `orders` + `customers` tables
- **Product Info**: `product_types` + `products` + `heads` (size/color)
- **Order Details**: `order_items` table
- **Manufacturing Stages**: `products.stage_ids` → `heads` table
- **Material Issuances**: `stock_items` + `igroup_items` tables
- **Material Details**: `materials` + `heads` (units) tables

### **2. Query Strategy**
```sql
-- Main PTC Data Query
SELECT
    o.order_no, o.job_no, o.order_date,
    c.customer_no, c.fname, c.lname,
    p.article_no, p.name as product_name, p.stage_ids,
    size.name as size_name, color.name as color_name,
    oi.quantity, oi.price
FROM orders o
JOIN customers c ON o.customer_id = c.customer_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN product_types pt ON oi.product_type_id = pt.product_type_id
JOIN products p ON pt.product_id = p.product_id
JOIN heads size ON pt.size_id = size.head_id
LEFT JOIN heads color ON pt.color_id = color.head_id
WHERE o.order_id = ? AND pt.product_type_id = ?

-- Material Issuances Query (Stock Items)
SELECT
    si.quantity, si.stage_id,
    m.material_no, m.name as material_name,
    stage.name as stage_name, unit.name as unit_name,
    s.stock_date, s.stock_no
FROM stock_items si
JOIN stocks s ON si.stock_id = s.stock_id
JOIN materials m ON si.material_id = m.material_id
JOIN heads stage ON si.stage_id = stage.head_id
JOIN heads unit ON m.unit_id = unit.head_id
WHERE s.order_id = ? AND si.product_type_id = ?

-- Material Issuances Query (IGroup Items)
SELECT
    ii.quantity, ii.stage_id,
    m.material_no, m.name as material_name,
    stage.name as stage_name, unit.name as unit_name,
    ig.igroup_date as stock_date, ig.igroup_no as stock_no
FROM igroup_items ii
JOIN igroups ig ON ii.igroup_id = ig.igroup_id
JOIN materials m ON ii.material_id = m.material_id
JOIN heads stage ON ii.stage_id = stage.head_id
JOIN heads unit ON m.unit_id = unit.head_id
WHERE ig.order_id = ? AND ii.product_type_id = ?
```

## 🔗 Integration Points

### **1. Order Management Integration**
Add PTC link to order details page:
```php
// In resources/views/orders/show.blade.php
@foreach($orderItems as $item)
    <tr>
        <td>{{ $item->product_name }}</td>
        <td>{{ $item->quantity }}</td>
        <td>
            <a href="{{ route('ptc.show', [$order->order_id, $item->product_type_id]) }}"
               class="btn btn-sm btn-info">View PTC</a>
        </td>
    </tr>
@endforeach
```

### **2. Stock Management Integration**
Add PTC reference in stock issuance pages:
```php
// In resources/views/stock/show.blade.php
<a href="{{ route('ptc.show', [$stock->order_id, $stockItem->product_type_id]) }}"
   class="btn btn-sm btn-secondary">View PTC</a>
```

## 🎯 Key Benefits

### **✅ Advantages of This Approach**
1. **No Database Changes**: Uses existing tables and relationships
2. **Real-time Data**: Always shows current issuance status
3. **Minimal Code**: Leverages existing infrastructure
4. **Easy Maintenance**: No additional data synchronization needed
5. **Flexible Reporting**: Can easily add more data points
6. **Cost Effective**: No migration or data restructuring required

### **📊 PTC Content Includes**
- **Order Information**: Order number, customer details, dates
- **Product Specifications**: Article number, size, color, quantity
- **Manufacturing Stages**: All stages in production sequence
- **Material Issuances**: Complete material usage per stage
- **Tracking Numbers**: Unique PTC number for identification
- **Source Tracking**: Whether materials came from stock or igroup issuance

## 🚀 Implementation Steps

1. **Install PDF Package**: `composer require barryvdh/laravel-dompdf`
2. **Create Controller**: Add `PTCController.php` with provided code
3. **Create Views**: Add the three view files (show, pdf, content)
4. **Add Routes**: Update `routes/web.php` with PTC routes
5. **Test**: Access PTC via `/ptc/{order_id}/{product_type_id}`
6. **Integrate**: Add PTC links to existing order and stock pages

## 📋 Sample PTC Output

### **PTC Number Format**: `PTC-2025-0016-001`
- **PTC**: Fixed prefix
- **2025**: Current year
- **0016**: Zero-padded order ID
- **001**: Zero-padded product type ID

### **Sample PTC Content**:
```
PRODUCT TRAVEL CARD
PTC-2025-0016-001

Order Information:
Order No: ORD-001    Job No: JOB-001
Customer: C001 - John Doe    Order Date: 2025-01-15

Product Information:
Article No: ART-001    Product Name: Surgical Gown
Size: Large    Color: Blue
Quantity: 100    Price: 1,500

Stage: Cutting
Date        Issue No    Material              Quantity    Unit    Source
2025-01-16  STK-001    MAT-001 - Cotton Fabric   50.00    Meters  Stock
2025-01-17  IG-001     MAT-002 - Thread          5.00     Rolls   Igroup

Stage: Stitching
Date        Issue No    Material              Quantity    Unit    Source
2025-01-18  STK-002    MAT-003 - Buttons        200.00    Pieces  Stock

Stage: Finishing
No materials issued for this stage yet.
```

This streamlined approach provides a comprehensive PTC system without any database modifications, leveraging the rich data already available in the existing ERP system.

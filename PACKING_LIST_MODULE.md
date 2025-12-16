# Packing List Module

## Overview
The Packing List module allows users to manually generate a Packing List from an existing Delivery record. The module provides drag-and-drop functionality to organize delivered products into carton groups.

## Features
- Create packing lists from deliveries
- Drag-and-drop interface for organizing products into carton groups
- Support for sequential carton numbering (e.g., Carton #1 or Cartons #5-10)
- Automatic calculation of total pieces based on cartons and pieces per carton
- View existing packing lists with detailed breakdown
- One packing list per delivery (enforced at database level)

## Database Schema

### Tables Created
1. **packing_lists**
   - packing_list_id (PK)
   - delivery_id (FK, unique)
   - order_id (FK)
   - created_by (FK)
   - timestamps

2. **packing_cartons**
   - packing_carton_id (PK)
   - packing_list_id (FK)
   - carton_from
   - carton_to
   - created_by (FK)
   - timestamps

3. **packing_carton_items**
   - packing_carton_item_id (PK)
   - packing_carton_id (FK)
   - product_id (FK)
   - pcs_each_carton
   - total_pcs
   - created_by (FK)
   - timestamps

## Files Created

### Migrations
- `database/migrations/2025_12_12_000001_create_packing_lists_table.php`
- `database/migrations/2025_12_12_000002_create_packing_cartons_table.php`
- `database/migrations/2025_12_12_000003_create_packing_carton_items_table.php`

### Models
- `app/Models/PackingList.php`
- `app/Models/PackingCarton.php`
- `app/Models/PackingCartonItem.php`

### Repository
- `app/Repositories/PackingListRepository.php`

### Controller
- `app/Http/Controllers/PackingListController.php`

### Views
- `resources/views/packingList.blade.php` (index/list)
- `resources/views/addPackingList.blade.php` (create with drag-and-drop)
- `resources/views/packingListInfo.blade.php` (view details)

### JavaScript
- `public/assets/js/packing-list.js` (drag-and-drop functionality)

### Routes
- GET `/packingList` - List all packing lists
- GET `/packingList/create/{delivery_id}` - Create new packing list
- POST `/packingList` - Store packing list
- GET `/packingList/{id}` - View packing list details

## Usage

### Creating a Packing List
1. Navigate to a Delivery Info page
2. Click "Create Packing List" button
3. Click "Add Carton Group" to create carton groups
4. Enter "Carton From" and "Carton To" numbers
5. Drag products from the left panel to the drop zone
6. Enter "Pcs Each Carton" for each product
7. Total pieces are calculated automatically
8. Click "Save Packing List"

### Viewing a Packing List
1. From Delivery Info page, click "View Packing List" button
2. Or navigate to Packing Lists index and click "View"

## Installation

Run the migrations:
```bash
php artisan migrate
```

## Technical Details

### Calculation Logic
```
total_pcs = pcs_each_carton × (carton_to - carton_from + 1)
```

### Form Submission Structure
```php
[
    'delivery_id' => 123,
    'groups' => [
        0 => [
            'carton_from' => 1,
            'carton_to' => 5,
            'products' => [
                0 => ['product_id' => 10, 'pcs_each_carton' => 50],
                1 => ['product_id' => 12, 'pcs_each_carton' => 30]
            ]
        ]
    ]
]
```

### Validation
- At least one carton group required
- Each carton group must have at least one product
- Carton To must be >= Carton From
- Pcs Each Carton must be >= 1
- Duplicate products in same carton group prevented
- One packing list per delivery enforced

## Dependencies
- Laravel Framework
- Vanilla JavaScript (HTML5 Drag and Drop API)
- Bootstrap (existing in project)
- Font Awesome (existing in project)


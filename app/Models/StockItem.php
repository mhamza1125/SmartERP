<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_item_id';

    protected $fillable = [
        'stock_id',
        'product_type_id',
        'material_id',
        'quantity',
        'stage_id',
        'rejected_stage_id', // Rejection lines: the work stage the pieces were rejected at
        'component_product_type_id',
        'ptc_virtual',
        'order_id',
        'work_logs',
        'work_wages',
        'created_by',
        'updated_at',
    ];

    /**
     * "No component" is NULL, never 0: general product stock selects product lines with
     * component_product_type_id IS NULL, so a 0 hides the line from /stock (F-13).
     */
    public function setComponentProductTypeIdAttribute($value): void
    {
        $this->attributes['component_product_type_id'] = empty($value) ? null : (int) $value;
    }
}

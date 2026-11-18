<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPriceLog extends Model
{
    protected $primaryKey = 'material_price_log_id';

    const SOURCE_OPENING = 'opening';
    const SOURCE_EDIT = 'edit';

    protected $fillable = [
        'material_id',
        'vendor_id',
        'price',
        'source',
        'created_by',
    ];
}

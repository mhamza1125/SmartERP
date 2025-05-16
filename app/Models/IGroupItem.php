<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IGroupItem extends Model
{
    use HasFactory;

    protected $table = 'igroup_items';

    protected $primaryKey = 'igroup_item_id';

    protected $fillable = [
        'igroup_id',
        'product_type_id',
        'material_id',
        'quantity',
        'stage_id',
        'created_by',
        'updated_at',
    ];
}

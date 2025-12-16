<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingCartonItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'packing_carton_item_id';

    protected $fillable = [
        'packing_carton_id',
        'product_id',
        'pcs_each_carton',
        'total_pcs',
        'created_by',
        'updated_at',
    ];

    public function packingCarton()
    {
        return $this->belongsTo(PackingCarton::class, 'packing_carton_id', 'packing_carton_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}


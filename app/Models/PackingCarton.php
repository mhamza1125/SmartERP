<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingCarton extends Model
{
    use HasFactory;

    protected $primaryKey = 'packing_carton_id';

    protected $fillable = [
        'packing_list_id',
        'carton_from',
        'carton_to',
        'created_by',
        'updated_at',
        'box_dimension',
        'box_weight',
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id', 'packing_list_id');
    }

    public function items()
    {
        return $this->hasMany(PackingCartonItem::class, 'packing_carton_id', 'packing_carton_id');
    }
}

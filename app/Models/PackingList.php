<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    use HasFactory;

    protected $primaryKey = 'packing_list_id';

    protected $fillable = [
        'delivery_id',
        'order_id',
        'created_by',
        'updated_at',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function cartons()
    {
        return $this->hasMany(PackingCarton::class, 'packing_list_id', 'packing_list_id');
    }
}


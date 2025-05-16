<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveMaterial extends Model
{
    use HasFactory;

    protected $primaryKey = 'receive_material_id';

    protected $fillable = [
        'receive_id',
        'purchase_item_id',
        'quantity',
        'pending_qty',
        'approved_qty',
        'rejected_qty',
        'inspection_date',
        'created_by',
        'updated_at',
    ];
}

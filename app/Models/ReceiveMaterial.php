<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveMaterial extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'receive_material_id';

    protected $fillable = [
        'purchase_item_id',
        'quantity',
        'receive_date',
        'inspection_status',
        'created_by',
        'updated_at',
    ];
}

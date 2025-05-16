<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MProcess extends Model
{
    use HasFactory;

    protected $table = 'mprocess';

    protected $primaryKey = 'mprocess_id';

    protected $fillable = [
        'purchase_id',
        'purchase_item_id',
        'stock_item_id',
        'before_mid',
        'before_qty',
        'created_by',
        'updated_at',
    ];
}

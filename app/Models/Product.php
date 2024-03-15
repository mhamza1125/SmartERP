<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'article_no',
        'name',
        'unit_id',
        'product_status',
        'description',
        'created_by',
        'updated_at',
    ];
}

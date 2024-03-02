<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'stock_no',
        'order_id',
        'department_id',
        'employee_id',
        'stock_type',
        'stock_date',
        'description',
        'created_by',
        'updated_at',
    ];
}

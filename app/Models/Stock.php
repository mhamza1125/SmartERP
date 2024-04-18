<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'issue_id',
        'stock_no', 
        'order_id',
        'table_name',
        'employee_id',
        'stock_type',
        'stock_date',
        'stock_status',
        'description',
        'created_by',
        'updated_at',
    ];
}

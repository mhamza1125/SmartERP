<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'vendor_id';

    protected $fillable = [
        'vendor_type_id',
        'vendor_no',
        'vendor_type',
        'material_id',
        'name',
        'fname',
        'phone1',
        'phone2',
        'city_id',
        'address',
        'description',
        'created_by',
        'updated_at',
    ];
}

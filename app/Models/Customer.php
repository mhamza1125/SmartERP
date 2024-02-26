<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_no',
        'fname',
        'lname',
        'email',
        'phone',
        'fax',
        'address',
        'description',
        'created_by',
        'updated_at',
    ];
}

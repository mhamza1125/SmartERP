<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $primaryKey = 'machine_id';

    protected $fillable = [
        'machine_no',
        'machine_type_id',
        'employee_id',
        'location',
        'description',
        'created_by',
        'updated_at',
    ];
}

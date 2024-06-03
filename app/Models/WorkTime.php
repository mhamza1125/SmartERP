<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    use HasFactory;

    protected $primaryKey = 'work_time_id';

    protected $fillable = [
        'work_time_id',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'grace_time',
        'description',
        'created_by',
        'updated_at',
    ];
}

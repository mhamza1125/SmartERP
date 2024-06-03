<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkHoliday extends Model
{
    use HasFactory;

    protected $primaryKey = 'work_holiday_id';

    protected $fillable = [
        'work_holiday_id',
        'date_from',
        'date_to',
        'description',
        'created_by',
        'updated_at',
    ];
}

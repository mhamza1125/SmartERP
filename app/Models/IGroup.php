<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IGroup extends Model
{
    use HasFactory;
    
    protected $table = 'igroups';
    protected $primaryKey = 'igroup_id';

    protected $fillable = [
        'igroup_no',
        'order_id',
        'igroup_date',
        'igroup_status',
        'description',
        'created_by',
        'updated_at',
    ];
}

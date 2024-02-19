<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ceo',
        'zip',
        'phone',
        'fax',
        'email',
        'website',
        'logo',
        'updated_at',
    ];
}

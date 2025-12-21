<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'name',
        'ceo',
        'ntn',
        'rex_no',
        'address',
        'city',
        'country',
        'zip',
        'phone',
        'fax',
        'email',
        'website',
        'logo',
        'logo_path',
        'footer_text',
        'statement_of_origin',
        'created_by',
        'updated_at',
    ];
}

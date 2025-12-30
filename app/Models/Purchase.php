<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    protected $primaryKey = 'purchase_id';

    protected $fillable = [
        'purchase_no',
        'purchase_type',
        'order_id',
        'vendor_id',
        'description',
        'purchase_date',
        'require_date',
        'created_by',
        'updated_at',
    ];

    protected function purchaseDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>
                $value ? Carbon::parse($value)->format('d-m-Y') : null,

            set: fn ($value) =>
                $value
                    ? Carbon::parse($value)->format('Y-m-d')
                    : null
        );
    }
    
    protected function requireDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>
                $value ? Carbon::parse($value)->format('d-m-Y') : null,

            set: fn ($value) =>
                $value
                    ? Carbon::parse($value)->format('Y-m-d')
                    : null
        );
    }
}

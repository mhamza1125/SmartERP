<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_no',
        'job_no',
        'customer_id',
        'order_status',
        'order_date',
        'due_date',
        'payment_terms',
        'expected_delivery_date',
        'description',
        'created_by',
        'updated_at',
    ];

    /**
     * Generate auto-generated order number with format: SLE-O001-25
     * SLE-O = Fixed prefix for orders
     * 001 = Sequential order number for the year (zero-padded, 3 digits)
     * 25 = Last 2 digits of the year (2025)
     */
    public static function generateOrderNo()
    {
        $currentYear = date('Y');
        $lastTwoDigits = substr($currentYear, -2);

        // Get the count of orders created this year
        $count = self::whereYear('created_at', $currentYear)->count();
        $sequentialNo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "SLE-O{$sequentialNo}-{$lastTwoDigits}";
    }
}

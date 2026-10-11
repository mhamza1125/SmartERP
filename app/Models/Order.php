<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    const STATUS_DRAFT = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_DISPATCHED = 3;
    const STATUS_DELIVERED = 4;
    const STATUS_CANCELLED = 5;

    const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_DISPATCHED => 'Dispatched',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    // Orders in these statuses cannot be edited (the status itself can still be changed)
    const LOCKED_STATUSES = [
        self::STATUS_DISPATCHED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public static function isEditableStatus($status): bool
    {
        return ! in_array((int) $status, self::LOCKED_STATUSES, true);
    }

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

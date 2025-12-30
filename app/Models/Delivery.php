<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $primaryKey = 'delivery_id';

    protected $fillable = [
        'stock_id',
        'fshipping',
        'tshipping',
        'fport_no',
        'tport_no',
        'delivery_method',
        'delivery_status',
        'fi_no',
        'delivery_no',
        'delivery_date',
        'created_by',
        'updated_at',
    ];

    /**
     * Generate auto-generated delivery number with format: SLE-D001-26
     * SLE-D = Fixed prefix for deliveries
     * 001 = Sequential delivery number for the year (zero-padded, 3 digits)
     * 26 = Last 2 digits of the year (2026)
     */
    public static function generateDeliveryNo()
    {
        $currentYear = date('Y');
        $lastTwoDigits = substr($currentYear, -2);

        // Get the count of deliveries created this year
        $count = self::whereYear('created_at', $currentYear)->count();
        $sequentialNo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "SLE-D{$sequentialNo}-{$lastTwoDigits}";
    }
}

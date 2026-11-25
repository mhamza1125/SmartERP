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

    /**
     * D5: allowed status moves (OrderRepository::assertStatusChange). Forward along
     * Draft -> Confirmed -> Dispatched -> Delivered (Dispatched may be skipped); Cancel only from
     * Draft / Confirmed. Dispatched / Delivered need a delivery; Cancelled needs no delivery and
     * no reserved stock. Moving back to an earlier status (re-open, incl. from Cancelled) is for
     * admins only and the target's conditions still apply. Anything else is refused.
     */
    const TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_DISPATCHED, self::STATUS_DELIVERED, self::STATUS_CANCELLED],
        self::STATUS_DISPATCHED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    /** Statuses a new order may be created with. */
    const INITIAL_STATUSES = [self::STATUS_DRAFT, self::STATUS_CONFIRMED];

    /** Position in the sequence; a move to a lower position is a re-open. Cancelled is terminal. */
    const SEQUENCE = [
        self::STATUS_DRAFT => 1,
        self::STATUS_CONFIRMED => 2,
        self::STATUS_DISPATCHED => 3,
        self::STATUS_DELIVERED => 4,
        self::STATUS_CANCELLED => 5,
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
        // Highest SLE-O number of the year (Asia/Karachi) + 1; locked inside a save, a preview otherwise
        return (new \App\Support\DocumentNumbers())->nextOrderNo(\Illuminate\Support\Facades\DB::transactionLevel() === 0);
    }
}

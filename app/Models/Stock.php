<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_id';

    // Stock Status Constants
    const STATUS_NOT_RECEIVED = 0;
    const STATUS_COMPLETELY_RECEIVED = 1;
    const STATUS_PARTIALLY_RECEIVED = 2;
    const STATUS_DELIVERY = 3;
    const STATUS_MACHINE_MATERIAL = 4;
    const STATUS_MATERIAL_PROCESSING = 5;
    const STATUS_PTC_IN_PROGRESS = 6;
    const STATUS_PTC_COMPLETED = 7; // PTC finished: its end stage was fully received
    const STATUS_PTC_RELEASED = 8;  // PTC virtual stock released to general stock
    const STATUS_PTC_CLOSED = 9;    // PTC stopped before its end stage (closed early)

    // table_name of a PTC release record (stock_type = 1, ptc_id = PTC master)
    const TABLE_PTC_RELEASE = 'ptc_release';

    // Stage head used for rejected pieces on receive forms
    const STAGE_REJECTED = 105;

    // What a PTC (master) starts from - stocks.start_from. NULL (older PTCs) = from stock.
    const START_FROM_STOCK = 'stock';         // stock at the start stage; first issuance is for the next stage
    const START_FROM_MATERIALS = 'materials'; // raw materials; first issuance is for the start stage itself

    protected $fillable = [
        'issue_id',
        'ptc_id',
        'current_stage_id',
        'start_stage_id',
        'start_from',
        'next_stage_id',
        'end_stage_id',
        'is_ptc_master',
        'stock_no',
        'issue_for',
        'order_id',
        'source_id',
        'machine_id',
        'table_name',
        'employee_id',
        'stock_type',
        'stock_date',
        'stock_status',
        'description',
        'created_by',
        'updated_at',
    ];

    /**
     * PTC issuance/receiving numbers are stored as '{ptcNo}-...' to keep
     * stock_no unique table-wide. Strip that prefix for display.
     * Legacy numbers without the prefix are returned unchanged.
     */
    /**
     * PTC started from raw materials: its start stage is its first work stage
     * (issued for and received into), not stock it already held.
     */
    public function startsFromMaterials(): bool
    {
        return $this->start_from === self::START_FROM_MATERIALS;
    }

    public static function ptcDisplayNo(?string $stockNo, ?string $ptcNo): string
    {
        $prefix = $ptcNo . '-';

        return ($ptcNo !== null && $ptcNo !== '' && str_starts_with((string) $stockNo, $prefix))
            ? substr($stockNo, strlen($prefix))
            : (string) $stockNo;
    }
}

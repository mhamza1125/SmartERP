<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Current price of an item for one vendor (buying) or customer (selling).
 * Item = material_id, or product_type_id + stage_id (unused keys are 0).
 */
class PartyPrice extends Model
{
    use LogsActivity;

    protected $primaryKey = 'party_price_id';

    const PARTY_VENDOR = 'vendor';

    const PARTY_CUSTOMER = 'customer';

    const SOURCE_MANUAL = 'manual';

    const SOURCE_PURCHASE = 'purchase';

    const SOURCE_ORDER = 'order';

    const SOURCE_BACKFILL = 'backfill';

    const SOURCE_REMOVED = 'removed';

    protected $fillable = [
        'party_type',
        'party_id',
        'material_id',
        'product_type_id',
        'stage_id',
        'price',
        'source',
        'doc_id',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One change of a product wage (product_costs row): general or employee / contractor specific. */
class ProductCostLog extends Model
{
    protected $primaryKey = 'product_cost_log_id';

    const ACTION_ADDED = 'added';

    const ACTION_CHANGED = 'changed';

    const ACTION_REMOVED = 'removed';

    const ACTION_OPENING = 'opening';

    protected $fillable = [
        'product_id',
        'head_id',
        'table_name',
        'table_id',
        'old_amount',
        'amount',
        'action',
        'created_by',
        'created_at',
        'updated_at',
    ];
}

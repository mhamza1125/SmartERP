<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One change of a vendor / customer price (see PartyPrice). */
class PartyPriceLog extends Model
{
    protected $primaryKey = 'party_price_log_id';

    protected $fillable = [
        'party_type',
        'party_id',
        'material_id',
        'product_type_id',
        'stage_id',
        'old_price',
        'price',
        'source',
        'doc_id',
        'created_by',
        'created_at',
        'updated_at',
    ];
}

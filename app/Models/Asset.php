<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One entry of the asset register (table `assets`, kind 'asset'). Entries with the same
 * asset_name make up one asset; its value = SUM(debit) - SUM(credit) (debit = increase,
 * credit = decrease). Liability (kind 'liability') uses the same table and rules.
 *
 * bank_id / transaction_id: set when the entry moved money (0 = cash, else a company bank
 * account) - the cash / bank transaction it posted. NULL = no money moved.
 */
class Asset extends Model
{
    use HasFactory;

    public const KIND = 'asset';

    /**
     * Entry types: sign = +1 increases the value, -1 decreases it; money = the way money moves
     * when the entry is paid / received through cash or bank ('out' = paid, 'in' = received),
     * null = the entry can never move money (depreciation, adjustments...).
     */
    public const TYPES = [
        'purchase' => ['label' => 'Purchase (Acquisition)', 'short' => 'Purchase', 'sign' => 1, 'money' => 'out'],
        'addition' => ['label' => 'Addition / Improvement', 'short' => 'Addition', 'sign' => 1, 'money' => 'out'],
        'sale' => ['label' => 'Sale (Disposal)', 'short' => 'Sale', 'sign' => -1, 'money' => 'in'],
        'depreciation' => ['label' => 'Depreciation', 'short' => 'Depreciation', 'sign' => -1, 'money' => null],
        'writeoff' => ['label' => 'Write-off', 'short' => 'Write-off', 'sign' => -1, 'money' => null],
        'adjustment' => ['label' => 'Adjustment (Increase)', 'short' => 'Adjustment +', 'sign' => 1, 'money' => null],
        'adjustment_decrease' => ['label' => 'Adjustment (Decrease)', 'short' => 'Adjustment -', 'sign' => -1, 'money' => null],
    ];

    protected $table = 'assets';

    protected $primaryKey = 'asset_id';

    protected $fillable = [
        'asset_name',
        'transaction_type',
        'description',
        'transaction_date',
        'debit',
        'credit',
        'bank_id',
        'transaction_id',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        $kind = static::KIND;
        static::addGlobalScope('kind', fn (Builder $query) => $query->where('assets.kind', $kind));
        static::creating(function (Asset $entry) use ($kind) {
            $entry->kind = $kind;
        });
    }
}

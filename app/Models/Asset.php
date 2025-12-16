<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $primaryKey = 'asset_id';

    protected $fillable = [
        'asset_name',
        'transaction_type',
        'description',
        'transaction_date',
        'debit',
        'credit',
        'attachment',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all transactions for a specific asset name
     * Groups by asset_name to show transaction history
     */
    public static function getAssetLedger($assetName = null)
    {
        $query = self::query();

        if ($assetName) {
            $query->where('asset_name', $assetName);
        }

        return $query->orderBy('transaction_date', 'asc')
                     ->orderBy('created_at', 'asc')
                     ->get();
    }

    /**
     * Calculate net asset value for a specific asset
     * Net Value = Total Debits - Total Credits
     */
    public static function calculateAssetValue($assetName)
    {
        $transactions = self::where('asset_name', $assetName)->get();

        $totalDebit = $transactions->sum('debit') ?? 0;
        $totalCredit = $transactions->sum('credit') ?? 0;

        return $totalDebit - $totalCredit;
    }

    /**
     * Get summary of all assets with their current values
     */
    public static function getAssetSummary()
    {
        return self::selectRaw('
                asset_name,
                COALESCE(SUM(debit), 0) as total_debit,
                COALESCE(SUM(credit), 0) as total_credit,
                COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net_value,
                MAX(transaction_date) as last_transaction_date
            ')
            ->groupBy('asset_name')
            ->orderBy('asset_name')
            ->get();
    }
}


<?php

namespace App\Repositories;

use App\Models\Liability;

/**
 * Liability register (Finance > Liabilities): the AssetRepository rules on Liability entries
 * (balance = outstanding amount; a loan received can be put into cash / bank, a repayment paid
 * from it).
 */
class LiabilityRepository extends AssetRepository
{
    protected string $model = Liability::class;
}

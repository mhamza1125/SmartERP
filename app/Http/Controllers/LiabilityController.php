<?php

namespace App\Http\Controllers;

use App\Models\Liability;
use App\Repositories\BankRepository;
use App\Repositories\LiabilityRepository;

/** Finance > Liabilities (screens in AssetEntryController). */
class LiabilityController extends AssetEntryController
{
    public function __construct(LiabilityRepository $repository, BankRepository $bankRepository)
    {
        parent::__construct($repository, $bankRepository);
    }

    protected function register(): array
    {
        return [
            'kind' => 'liability',
            'model' => Liability::class,
            'route' => 'liability',
            'title' => 'Liability',
            'plural' => 'Liabilities',
            'increase' => 'Increases',
            'decrease' => 'Payments / Reductions',
            'balance' => 'Outstanding',
            'nameHint' => 'e.g. "Bank Loan - Meezan", "Car Lease". Entries with the same name make up one liability.',
        ];
    }
}

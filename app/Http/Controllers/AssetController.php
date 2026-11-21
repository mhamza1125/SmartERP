<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Repositories\AssetRepository;
use App\Repositories\BankRepository;

/** Finance > Assets (screens in AssetEntryController). */
class AssetController extends AssetEntryController
{
    public function __construct(AssetRepository $repository, BankRepository $bankRepository)
    {
        parent::__construct($repository, $bankRepository);
    }

    protected function register(): array
    {
        return [
            'kind' => 'asset',
            'model' => Asset::class,
            'route' => 'asset',
            'title' => 'Asset',
            'plural' => 'Assets',
            'increase' => 'Additions',
            'decrease' => 'Reductions',
            'balance' => 'Net Value',
            'nameHint' => 'e.g. "Office Computer", "Delivery Van". Entries with the same name make up one asset.',
        ];
    }
}

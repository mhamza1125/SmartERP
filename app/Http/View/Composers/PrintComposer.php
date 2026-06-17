<?php

namespace App\Http\View\Composers;

use App\Models\Company;
use Illuminate\View\View;

class PrintComposer
{
    protected ?Company $company;

    public function __construct()
    {
        $this->company = Company::first();
    }

    public function compose(View $view): void
    {
        $view->with('company', $this->company);
    }
}

<?php

namespace App\Providers;

use App\Http\View\Composers\PrintComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('print.*', PrintComposer::class);
    }
}

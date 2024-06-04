<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // These are only to Show / Hide Buttons by @can Method for .blade
        // To Restrict user access these must be used in Controller Methods

        Gate::define('show', function (User $user) {
            $allowedRoles = ['admin', 'operator'];
            return in_array($user->role, $allowedRoles);
        });

        Gate::define('create', function (User $user) {
            $allowedRoles = ['admin', 'operator'];
            return in_array($user->role, $allowedRoles);
        });

        Gate::define('edit', function (User $user) {
            $allowedRoles = ['admin'];
            return in_array($user->role, $allowedRoles);
        });

        Gate::define('delete', function (User $user) {
            $allowedRoles = ['admin'];
            return in_array($user->role, $allowedRoles);
        });
    }
}

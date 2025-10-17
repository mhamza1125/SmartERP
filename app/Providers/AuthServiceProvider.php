<?php

namespace App\Providers;

use App\Models\Bank;
use App\Models\User;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Machine;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\Attendance;
use App\Models\Settings;
use App\Models\Transaction;
use App\Policies\BankPolicy;
use App\Policies\AdminPolicy;
use App\Policies\OrderPolicy;
use App\Policies\StockPolicy;
use App\Policies\VendorPolicy;
use App\Policies\MachinePolicy;
use App\Policies\ProductPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DeliveryPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\MaterialPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\AttendancePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\TransactionPolicy;
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
        User::class => AdminPolicy::class,
        Material::class => MaterialPolicy::class,
        Bank::class => BankPolicy::class,
        Product::class => ProductPolicy::class,
        Customer::class => CustomerPolicy::class,
        Employee::class => EmployeePolicy::class,
        Vendor::class => VendorPolicy::class,
        Delivery::class => DeliveryPolicy::class,
        Machine::class => MachinePolicy::class,
        Order::class => OrderPolicy::class,
        Stock::class => StockPolicy::class,
        Purchase::class => PurchasePolicy::class,
        Transaction::class => TransactionPolicy::class,
        Attendance::class => AttendancePolicy::class,
        Settings::class => SettingsPolicy::class,
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

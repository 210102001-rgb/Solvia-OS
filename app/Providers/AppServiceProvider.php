<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin has implicit full access to everything
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Strict Super Admin boundaries for high-risk operations
        Gate::define('access-payroll', fn(User $user) => $user->isSuperAdmin());
        Gate::define('access-finance', fn(User $user) => $user->isSuperAdmin() || $user->hasPermission('finance.view'));
        Gate::define('access-audit-logs', fn(User $user) => $user->isSuperAdmin() || $user->hasPermission('audit.view'));
        Gate::define('manage-approvals', fn(User $user) => $user->isSuperAdmin());
        Gate::define('manage-company', fn(User $user) => $user->isSuperAdmin());
        Gate::define('manage-automations', fn(User $user) => $user->isSuperAdmin());

        // Granular fallback gate
        Gate::define('has-permission', fn(User $user, string $permission) => $user->hasPermission($permission));
    }
}

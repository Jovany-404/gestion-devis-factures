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
        Gate::define('manage-documents', fn (User $user): bool => $user->canManageDocuments());
        Gate::define('manage-catalog', fn (User $user): bool => $user->canManageDocuments());
        Gate::define('manage-company', fn (User $user): bool => $user->isAdministrator());
        Gate::define('manage-staff', fn (User $user): bool => $user->isAdministrator());
        Gate::define('record-payment', fn (User $user): bool => in_array(
            $user->role,
            [User::ROLE_ADMIN, User::ROLE_ACCOUNTANT],
            true
        ));
        Gate::define('access-staff', fn (User $user): bool => $user->canAccessStaffWorkspace());
        Gate::define('access-client', fn (User $user): bool => $user->canAccessClientPortal());
        Gate::define('access-api', fn (User $user): bool => $user->canAccessStaffWorkspace());
    }
}

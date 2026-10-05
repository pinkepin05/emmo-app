<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use App\Models\Role;
use App\Observers\UserObserver;
use App\Observers\RoleObserver;

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
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap(\App\Services\BsUsageCheckService::TYPES);

        // Observer: sinkronisasi otomatis role master <-> Spatie
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);

        // 🎯 HAK APPROVE ATASAN = BERASAL DARI RELASI, BUKAN DARI ROLE
        // Bernilai true hanya jika user ini merupakan atasan dari minimal 1 user lain
        Gate::define('approval-atasan', function (User $user) {
            return User::where('atasan_user_id', $user->id)->exists();
        });

        // 🎯 Pagination bergaya Bootstrap 5 (bukan default Tailwind Laravel)
        Paginator::useBootstrapFive();
    }
}

<?php

namespace App\Providers;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureEstablishmentAccess;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\EnsurePlanLimit;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\EnsureWarehouseAccess;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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
        $this->configureDefaults();
        Livewire::addPersistentMiddleware([
            EnsureActiveUser::class,
            EnsureEmailIsVerified::class,
            EnsureTenant::class,
            EnsureTenantActive::class,
            EnsureSubscriptionActive::class,
            EnsureModuleEnabled::class,
            EnsurePlanLimit::class,
            EnsureEstablishmentAccess::class,
            EnsureWarehouseAccess::class,
            CheckRole::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

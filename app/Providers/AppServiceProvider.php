<?php

namespace App\Providers;

use App\Models\ConfiguracionPlataforma;
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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;
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
        $this->loadPlatformSettings();
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

    protected function loadPlatformSettings(): void
    {
        try {
            if (! Schema::hasTable('configuraciones_plataforma')) {
                return;
            }

            $ajustes = ConfiguracionPlataforma::query()->pluck('valor', 'clave');
            if (filled($ajustes->get('nombre_comercial'))) {
                config(['app.name' => $ajustes->get('nombre_comercial')]);
            }
            if (filled($ajustes->get('correo_soporte'))) {
                config(['mail.from.address' => $ajustes->get('correo_soporte')]);
            }
            if (filled($ajustes->get('zona_horaria')) && in_array($ajustes->get('zona_horaria'), timezone_identifiers_list(), true)) {
                config(['app.timezone' => $ajustes->get('zona_horaria')]);
                date_default_timezone_set($ajustes->get('zona_horaria'));
            }
        } catch (Throwable) {
            // Fresh installations may not have a configured database until migrations run.
        }
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

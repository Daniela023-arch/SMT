<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureInventoryType;
use App\Http\Middleware\EnsureModuleCompatibleWithInventory;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\EnsurePlanLimit;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\EnsureTenantResource;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureActiveUser::class,
            'tenant' => EnsureTenant::class,
            'tenant.active' => EnsureTenantActive::class,
            'subscription.active' => EnsureSubscriptionActive::class,
            'module' => EnsureModuleEnabled::class,
            'module.compatible' => EnsureModuleCompatibleWithInventory::class,
            'inventory.type' => EnsureInventoryType::class,
            'tenant.resource' => EnsureTenantResource::class,
            'plan.limit' => EnsurePlanLimit::class,
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

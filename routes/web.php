<?php

use App\Livewire\Establecimientos\Index as EstablecimientosIndex;
use App\Livewire\Inventario\Show as InventarioShow;
use App\Livewire\Publico\Inicio as PublicInicio;
use App\Livewire\Publico\Solicitud as PublicSolicitud;
use App\Livewire\Productos\Index as ProductosIndex;
use App\Livewire\Superadministrador\Dashboard as SuperadminDashboard;
use App\Livewire\Superadministrador\Modulos as SuperadminModulos;
use App\Livewire\Superadministrador\Planes as SuperadminPlanes;
use App\Livewire\Superadministrador\Promociones as SuperadminPromociones;
use App\Livewire\Superadministrador\Reportes as SuperadminReportes;
use App\Livewire\Superadministrador\Solicitudes as SuperadminSolicitudes;
use App\Livewire\Superadministrador\Suscripciones as SuperadminSuscripciones;
use App\Livewire\Superadministrador\Tenants as SuperadminTenants;
use App\Livewire\Superadministrador\Usuarios as SuperadminUsuarios;
use App\Livewire\Superadministrador\Configuracion as SuperadminConfiguracion;
use App\Livewire\Transferencias\Index as TransferenciasIndex;
use App\Livewire\Ventas\Index as VentasIndex;
use Illuminate\Support\Facades\Route;

Route::livewire('/', PublicInicio::class)->name('home');
Route::livewire('/solicitar', PublicSolicitud::class)->name('public.solicitar');

Route::middleware(['auth', 'active', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        $user = request()->user();
        $isSuperadmin = $user->roles()
            ->where('roles.slug', 'superadmin')
            ->whereNull('roles.tenant_id')
            ->wherePivot('tenant_id', null)
            ->exists();

        return $isSuperadmin ? redirect()->route('superadmin.dashboard') : view('dashboard');
    })->name('dashboard');

    Route::prefix('superadmin')->middleware('role:superadmin')->group(function (): void {
        Route::livewire('/', SuperadminDashboard::class)->name('superadmin.dashboard');
        Route::livewire('/solicitudes', SuperadminSolicitudes::class)->name('superadmin.solicitudes');
        Route::livewire('/tenants', SuperadminTenants::class)->name('superadmin.tenants');
        Route::livewire('/planes', SuperadminPlanes::class)->name('superadmin.planes');
        Route::livewire('/suscripciones', SuperadminSuscripciones::class)->name('superadmin.suscripciones');
        Route::livewire('/modulos', SuperadminModulos::class)->name('superadmin.modulos');
        Route::livewire('/usuarios', SuperadminUsuarios::class)->name('superadmin.usuarios');
        Route::livewire('/promociones', SuperadminPromociones::class)->name('superadmin.promociones');
        Route::livewire('/reportes', SuperadminReportes::class)->name('superadmin.reportes');
        Route::livewire('/configuracion', SuperadminConfiguracion::class)->name('superadmin.configuracion');
    });

    Route::middleware(['tenant', 'tenant.active', 'subscription.active'])->group(function (): void {
        Route::livewire('establecimientos', EstablecimientosIndex::class)
            ->middleware(['role:admin,ventas,almacen', 'module:sucursales'])
            ->name('establecimientos.index');

        Route::livewire('productos', ProductosIndex::class)
            ->middleware(['role:admin,ventas,almacen', 'module:productos'])
            ->name('productos.index');

        Route::livewire('establecimientos/{establecimiento}/almacenes/{almacen}/inventario', InventarioShow::class)
            ->middleware(['role:admin,almacen,ventas', 'establishment:establecimiento', 'warehouse:almacen', 'module:stock'])
            ->name('inventario.show');

        Route::livewire('establecimientos/{establecimiento}/almacenes/{almacen}/ventas', VentasIndex::class)
            ->middleware(['role:admin,ventas', 'establishment:establecimiento', 'warehouse:almacen', 'module:ventas'])
            ->name('ventas.index');

        Route::livewire('transferencias', TransferenciasIndex::class)
            ->middleware(['role:admin,almacen', 'module:transferencias'])
            ->name('transferencias.index');
    });
});

require __DIR__.'/settings.php';

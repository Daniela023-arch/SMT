<?php

use App\Livewire\Establecimientos\Index as EstablecimientosIndex;
use App\Livewire\Inventario\Show as InventarioShow;
use App\Livewire\Productos\Index as ProductosIndex;
use App\Livewire\Transferencias\Index as TransferenciasIndex;
use App\Livewire\Ventas\Index as VentasIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function (): void {
    Route::view('dashboard', 'dashboard')->name('dashboard');

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

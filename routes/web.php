<?php

use App\Http\Controllers\Api\EstablecimientoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\TransferenciaController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('api/v1')
        ->name('api.v1.')
        ->middleware(['tenant', 'tenant.active', 'subscription.active'])
        ->group(function (): void {
            Route::get('establecimientos', [EstablecimientoController::class, 'index'])
                ->middleware(['role:admin,ventas,almacen', 'module:sucursales'])
                ->name('establecimientos.index');
            Route::post('establecimientos', [EstablecimientoController::class, 'store'])
                ->middleware(['role:admin', 'module:sucursales'])
                ->name('establecimientos.store');
            Route::post('establecimientos/{establecimiento}/almacenes', [EstablecimientoController::class, 'storeAlmacen'])
                ->middleware(['role:admin', 'establishment:establecimiento', 'module:almacenes'])
                ->name('establecimientos.almacenes.store');
            Route::post('establecimientos/{establecimiento}/almacenes/{almacen}/ubicaciones', [EstablecimientoController::class, 'storeUbicacion'])
                ->middleware(['role:admin,almacen', 'establishment:establecimiento', 'warehouse:almacen', 'module:ubicaciones'])
                ->name('almacenes.ubicaciones.store');
            Route::get('establecimientos/{establecimiento}/usuarios', [EstablecimientoController::class, 'usuarios'])
                ->middleware(['role:admin,ventas,almacen', 'establishment:establecimiento', 'module:sucursales'])
                ->name('establecimientos.usuarios.index');
            Route::put('establecimientos/{establecimiento}/usuarios', [EstablecimientoController::class, 'asignarUsuario'])
                ->middleware(['role:admin', 'establishment:establecimiento', 'module:usuarios'])
                ->name('establecimientos.usuarios.assign');

            Route::get('productos', [ProductoController::class, 'index'])
                ->middleware(['role:admin,ventas,almacen', 'module:productos'])
                ->name('productos.index');
            Route::post('productos', [ProductoController::class, 'store'])
                ->middleware(['role:admin', 'module:productos', 'plan.limit:productos'])
                ->name('productos.store');

            Route::prefix('establecimientos/{establecimiento}/almacenes/{almacen}')
                ->middleware(['establishment:establecimiento', 'warehouse:almacen'])
                ->group(function (): void {
                    Route::get('existencias', [InventarioController::class, 'existencias'])
                        ->middleware(['role:admin,ventas,almacen', 'module:stock'])
                        ->name('almacenes.existencias.index');
                    Route::get('movimientos', [InventarioController::class, 'movimientos'])
                        ->middleware(['role:admin,almacen', 'module:stock'])
                        ->name('almacenes.movimientos.index');
                    Route::post('entradas', [InventarioController::class, 'entrada'])
                        ->middleware(['role:admin,almacen', 'module:stock'])
                        ->name('almacenes.entradas.store');
                    Route::post('salidas', [InventarioController::class, 'salida'])
                        ->middleware(['role:admin,almacen', 'module:stock'])
                        ->name('almacenes.salidas.store');

                    Route::get('ventas', [VentaController::class, 'index'])
                        ->middleware(['role:admin,ventas', 'module:ventas'])
                        ->name('ventas.index');
                    Route::post('ventas', [VentaController::class, 'store'])
                        ->middleware(['role:admin,ventas', 'module:ventas'])
                        ->name('ventas.store');
                });

            Route::post('ventas/{venta}/confirmar', [VentaController::class, 'confirmar'])
                ->middleware(['role:admin,ventas', 'module:ventas'])
                ->name('ventas.confirmar');
            Route::get('ventas/{venta}', [VentaController::class, 'show'])
                ->middleware(['role:admin,ventas', 'module:ventas'])
                ->name('ventas.show');
            Route::post('ventas/{venta}/pagos', [VentaController::class, 'registrarPago'])
                ->middleware(['role:admin,ventas', 'module:ventas'])
                ->name('ventas.pagos.store');

            Route::get('transferencias', [TransferenciaController::class, 'index'])
                ->middleware(['role:admin,almacen', 'module:transferencias'])
                ->name('transferencias.index');
            Route::post('transferencias', [TransferenciaController::class, 'store'])
                ->middleware(['role:admin,almacen', 'module:transferencias'])
                ->name('transferencias.store');
            Route::get('transferencias/{transferencia}', [TransferenciaController::class, 'show'])
                ->middleware(['role:admin,almacen', 'module:transferencias'])
                ->name('transferencias.show');
            Route::post('transferencias/{transferencia}/autorizar', [TransferenciaController::class, 'autorizar'])
                ->middleware(['role:admin', 'module:transferencias'])
                ->name('transferencias.autorizar');
            Route::post('transferencias/{transferencia}/rechazar', [TransferenciaController::class, 'rechazar'])
                ->middleware(['role:admin', 'module:transferencias'])
                ->name('transferencias.rechazar');
            Route::post('transferencias/{transferencia}/despachar', [TransferenciaController::class, 'despachar'])
                ->middleware(['role:admin,almacen', 'module:transferencias'])
                ->name('transferencias.despachar');
            Route::post('transferencias/{transferencia}/recibir', [TransferenciaController::class, 'recibir'])
                ->middleware(['role:admin,almacen', 'module:transferencias'])
                ->name('transferencias.recibir');
            Route::post('transferencias/{transferencia}/cancelar', [TransferenciaController::class, 'cancelar'])
                ->middleware(['role:admin', 'module:transferencias'])
                ->name('transferencias.cancelar');
        });
});

require __DIR__.'/settings.php';

<?php

use App\Models\Modulo;
use App\Services\ModuloTenantService;
use Illuminate\Validation\ValidationException;

it('habilita y deshabilita módulos autorizados para un tenant', function () {
    $tenant = $this->serviceTenant();
    Modulo::query()->create(['clave' => 'ventas', 'nombre' => 'Ventas', 'activo' => true]);
    $service = app(ModuloTenantService::class);

    expect($service->habilitar($tenant->id, 'ventas')->habilitado)->toBeTrue()
        ->and($service->habilitar($tenant->id, 'ventas', false)->habilitado)->toBeFalse();
});

it('no habilita un módulo inexistente o globalmente desactivado', function () {
    $tenant = $this->serviceTenant();
    Modulo::query()->create(['clave' => 'bajas', 'nombre' => 'Bajas', 'activo' => false]);

    expect(fn () => app(ModuloTenantService::class)->habilitar($tenant->id, 'bajas'))
        ->toThrow(ValidationException::class);
});

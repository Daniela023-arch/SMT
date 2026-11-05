<?php

use App\Models\User;
use App\Models\Ubicacion;
use App\Services\EstablecimientoService;
use Illuminate\Validation\ValidationException;

it('crea sucursales y almacenes con ubicación principal', function () {
    $tenant = $this->serviceTenant();
    $service = app(EstablecimientoService::class);
    $branch = $service->crear($tenant->id, [
        'codigo' => 'SUC-01',
        'nombre' => 'Sucursal Centro',
        'tipo' => 'sucursal',
    ]);
    $warehouse = $service->crearAlmacen($tenant->id, $branch->id, 'ALM-01', 'Almacén central');

    expect($warehouse->establecimiento_id)->toBe($branch->id)
        ->and(Ubicacion::query()->where('almacen_id', $warehouse->id)->value('nombre'))->toBe('Principal');
});

it('asigna usuarios solo a establecimientos de su tenant y valida los tipos permitidos', function () {
    $tenant = $this->serviceTenant();
    $otherTenant = $this->serviceTenant('Farmacia');
    $branch = $this->serviceEstablishment($tenant);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $service = app(EstablecimientoService::class);

    $service->asignarUsuario($tenant->id, $user->id, $branch->id);

    expect($user->establecimientos()->count())->toBe(1)
        ->and(fn () => $service->asignarUsuario($tenant->id, $user->id, $this->serviceEstablishment($otherTenant)->id))
        ->toThrow(ValidationException::class)
        ->and(fn () => $service->crear($tenant->id, ['codigo' => 'BAD', 'nombre' => 'Inválido', 'tipo' => 'bodega']))
        ->toThrow(ValidationException::class);
});

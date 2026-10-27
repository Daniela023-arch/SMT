<?php

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use App\Services\AccesoTenantService;
use Illuminate\Validation\ValidationException;

it('asigna roles solo cuando el usuario y el rol corresponden al tenant', function () {
    $tenant = $this->serviceTenant();
    $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
    $role = Rol::query()->create(['tenant_id' => $tenant->getKey(), 'nombre' => 'Ventas', 'slug' => 'ventas']);

    $assignment = app(AccesoTenantService::class)->asignarRol($user->getKey(), $role->getKey(), $tenant->getKey());

    expect($assignment->tenant_id)->toBe($tenant->getKey());
});

it('deniega roles de otro tenant y valida permisos asignados al rol', function () {
    $tenant = $this->serviceTenant();
    $otherTenant = $this->serviceTenant('Farmacia');
    $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
    $role = Rol::query()->create(['tenant_id' => $tenant->getKey(), 'nombre' => 'Almacén', 'slug' => 'almacen']);
    $permission = Permiso::query()->create(['clave' => 'inventario.entrada']);
    $role->permisos()->attach($permission->getKey());
    $service = app(AccesoTenantService::class);

    expect(fn () => $service->asignarRol($user->getKey(), $role->getKey(), $otherTenant->getKey()))
        ->toThrow(ValidationException::class)
        ->and($service->tienePermiso($user, 'inventario.entrada', $tenant->getKey()))->toBeFalse();

    $service->asignarRol($user->getKey(), $role->getKey(), $tenant->getKey());
    expect($service->tienePermiso($user, 'inventario.entrada', $tenant->getKey()))->toBeTrue();

    $user->forceFill(['activo' => false])->save();
    expect($service->tienePermiso($user, 'inventario.entrada', $tenant->getKey()))->toBeFalse();
});

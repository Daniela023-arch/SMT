<?php

use App\Models\SolicitudInventario;
use App\Models\TipoInventario;
use App\Services\TenantService;
use Illuminate\Validation\ValidationException;

it('crea un tenant con configuración inicial', function () {
    $type = TipoInventario::query()->create(['nombre' => 'Biblioteca']);

    $tenant = app(TenantService::class)->crear('Biblioteca Central', $type->id, 'contacto@example.com');

    expect($tenant->estado)->toBe('pendiente')
        ->and($tenant->configuracionInventario)->not->toBeNull()
        ->and($tenant->configuracionInventario->permitir_stock_negativo)->toBeFalse();
});

it('aprueba una solicitud una sola vez y valida cambios de estado', function () {
    $type = TipoInventario::query()->create(['nombre' => 'Farmacia']);
    $request = SolicitudInventario::query()->create([
        'tipo_inventario_id' => $type->id,
        'nombre_negocio' => 'Farmacia Norte',
        'email_contacto' => 'norte@example.com',
        'estado' => 'pendiente',
    ]);
    $service = app(TenantService::class);
    $tenant = $service->aprobarSolicitud($request->id);

    expect($tenant->estado)->toBe('pendiente')
        ->and($request->fresh()->estado)->toBe('aprobada')
        ->and($request->fresh()->tenant_id)->toBe($tenant->id);

    expect(fn () => $service->aprobarSolicitud($request->id))->toThrow(ValidationException::class)
        ->and(fn () => $service->cambiarEstado($tenant->id, 'invalido'))->toThrow(ValidationException::class)
        ->and($service->cambiarEstado($tenant->id, 'activo')->estado)->toBe('activo');
});

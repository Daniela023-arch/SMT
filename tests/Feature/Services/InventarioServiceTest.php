<?php

use App\Models\Existencia;
use App\Services\InventarioService;
use Illuminate\Validation\ValidationException;

it('registra entradas, salidas y transferencias manteniendo el saldo por ubicación', function () {
    $tenant = $this->serviceTenant();
    $product = $this->serviceProduct($tenant);
    $source = $this->serviceLocation($tenant, 'Origen');
    $destination = $this->serviceLocation($tenant, 'Destino');
    $service = app(InventarioService::class);

    $service->registrarEntrada($tenant->id, $product->id, $source->id, 10);
    $service->transferir($tenant->id, $product->id, $source->id, $destination->id, 4);
    $service->registrarSalida($tenant->id, $product->id, $destination->id, 1);

    expect((float) Existencia::query()->where('ubicacion_id', $source->id)->value('cantidad'))->toBe(6.0)
        ->and((float) Existencia::query()->where('ubicacion_id', $destination->id)->value('cantidad'))->toBe(3.0)
        ->and($product->stockMovimientos()->count())->toBe(3);
});

it('rechaza salidas sin existencias y productos de otro tenant', function () {
    $tenant = $this->serviceTenant();
    $otherTenant = $this->serviceTenant('Farmacia');
    $product = $this->serviceProduct($tenant);
    $otherProduct = $this->serviceProduct($otherTenant);
    $location = $this->serviceLocation($tenant);
    $service = app(InventarioService::class);

    expect(fn () => $service->registrarSalida($tenant->id, $product->id, $location->id, 1))
        ->toThrow(ValidationException::class)
        ->and(fn () => $service->registrarEntrada($tenant->id, $otherProduct->id, $location->id, 1))
        ->toThrow(ValidationException::class);
});

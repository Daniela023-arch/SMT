<?php

use App\Models\Baja;
use App\Models\DetalleBaja;
use App\Services\BajaService;
use App\Services\InventarioService;
use Illuminate\Validation\ValidationException;

it('aprueba una baja, descuenta existencias y registra el movimiento', function () {
    $tenant = $this->serviceTenant();
    $product = $this->serviceProduct($tenant);
    $location = $this->serviceLocation($tenant);
    app(InventarioService::class)->registrarEntrada($tenant->id, $product->id, $location->id, 5);
    $baja = Baja::query()->create(['tenant_id' => $tenant->id, 'folio' => 'BAJA-1', 'motivo' => 'daño']);
    DetalleBaja::query()->create(['tenant_id' => $tenant->id, 'baja_id' => $baja->id, 'producto_id' => $product->id, 'cantidad' => 2]);

    $result = app(BajaService::class)->aprobar($baja->id, $location->id);

    expect($result->estado)->toBe('aprobada')
        ->and((float) $product->existencias()->first()->cantidad)->toBe(3.0);
});

it('no permite aprobar una baja sin detalles', function () {
    $tenant = $this->serviceTenant();
    $baja = Baja::query()->create(['tenant_id' => $tenant->id, 'folio' => 'BAJA-2', 'motivo' => 'daño']);

    expect(fn () => app(BajaService::class)->aprobar($baja->id, $this->serviceLocation($tenant)->id))
        ->toThrow(ValidationException::class);
});

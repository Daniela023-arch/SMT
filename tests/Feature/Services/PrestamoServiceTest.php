<?php

use App\Models\DetallePrestamo;
use App\Models\Existencia;
use App\Models\Prestamo;
use App\Services\InventarioService;
use App\Services\PrestamoService;
use Illuminate\Validation\ValidationException;

it('presta una unidad disponible y la recibe de regreso', function () {
    $tenant = $this->serviceTenant('Biblioteca');
    $product = $this->serviceProduct($tenant);
    $location = $this->serviceLocation($tenant);
    $unit = $this->serviceUnit($tenant, $product, $location, ['numero_serie' => 'SERIE-1']);
    app(InventarioService::class)->registrarEntrada($tenant->id, $product->id, $location->id, 1, unidadInventarioId: $unit->id);
    $loan = Prestamo::query()->create([
        'tenant_id' => $tenant->id,
        'folio' => 'P-100',
        'prestatario_nombre' => 'Ana',
        'fecha_prestamo' => today(),
        'fecha_limite' => today()->addWeek(),
    ]);
    $service = app(PrestamoService::class);
    $detail = $service->prestarUnidad($loan, $unit->id);

    expect($detail->estado)->toBe('prestado')
        ->and($unit->fresh()->estado)->toBe('prestada')
        ->and((float) Existencia::query()->where('producto_id', $product->id)->value('cantidad'))->toBe(0.0)
        ->and($service->recibirUnidad($detail)->estado)->toBe('devuelto')
        ->and($unit->fresh()->estado)->toBe('disponible')
        ->and((float) Existencia::query()->where('producto_id', $product->id)->value('cantidad'))->toBe(1.0);
});

it('no permite prestar una unidad que no está disponible', function () {
    $tenant = $this->serviceTenant('Biblioteca');
    $unit = $this->serviceUnit($tenant, $this->serviceProduct($tenant), $this->serviceLocation($tenant), ['estado' => 'prestada']);
    $loan = Prestamo::query()->create([
        'tenant_id' => $tenant->id,
        'folio' => 'P-101',
        'prestatario_nombre' => 'Luis',
        'fecha_prestamo' => today(),
        'fecha_limite' => today()->addWeek(),
    ]);

    expect(fn () => app(PrestamoService::class)->prestarUnidad($loan, $unit->id))->toThrow(ValidationException::class);
});

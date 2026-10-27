<?php

use App\Models\Venta;
use App\Services\PagoVentaService;
use Illuminate\Validation\ValidationException;

it('registra un pago válido contra una venta confirmada', function () {
    $tenant = $this->serviceTenant();
    $venta = Venta::query()->create(['tenant_id' => $tenant->id, 'folio' => 'V-100', 'estado' => 'confirmada', 'total' => 100]);

    $pago = app(PagoVentaService::class)->registrar($venta->id, '40.00', 'efectivo', 'REF-100');

    expect((float) $pago->monto)->toBe(40.0)
        ->and($pago->estado)->toBe('pagado')
        ->and($pago->tenant_id)->toBe($tenant->id);
});

it('rechaza un pago que excede el saldo o duplica una referencia', function () {
    $tenant = $this->serviceTenant();
    $venta = Venta::query()->create(['tenant_id' => $tenant->id, 'folio' => 'V-101', 'estado' => 'confirmada', 'total' => 20]);
    $service = app(PagoVentaService::class);
    $service->registrar($venta->id, '15.00', 'tarjeta', 'REF-101');

    expect(fn () => $service->registrar($venta->id, '6.00', 'efectivo'))
        ->toThrow(ValidationException::class)
        ->and(fn () => $service->registrar($venta->id, '1.00', 'efectivo', 'REF-101'))
        ->toThrow(ValidationException::class);
});

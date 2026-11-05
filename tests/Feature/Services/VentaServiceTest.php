<?php

use App\Models\DetalleVenta;
use App\Models\Existencia;
use App\Models\Venta;
use App\Services\InventarioService;
use App\Services\VentaService;
use Illuminate\Validation\ValidationException;

it('confirma una venta y descuenta las cantidades de inventario', function () {
    $tenant = $this->serviceTenant();
    $product = $this->serviceProduct($tenant);
    $branch = $this->serviceEstablishment($tenant);
    $warehouse = $this->serviceWarehouse($tenant, $branch);
    app(InventarioService::class)->registrarEntradaEnAlmacen($tenant->id, $product->id, $warehouse->id, 5);
    $sale = Venta::query()->create([
        'tenant_id' => $tenant->id,
        'establecimiento_id' => $branch->id,
        'almacen_id' => $warehouse->id,
        'folio' => 'V-200',
        'estado' => 'borrador',
        'total' => 20,
    ]);
    DetalleVenta::query()->create([
        'tenant_id' => $tenant->id, 'venta_id' => $sale->id, 'producto_id' => $product->id,
        'cantidad' => 2, 'precio_unitario' => 10, 'total' => 20,
    ]);

    expect(app(VentaService::class)->confirmar($sale)->estado)->toBe('confirmada')
        ->and((float) Existencia::query()->where('producto_id', $product->id)->value('cantidad'))->toBe(3.0);
});

it('no confirma ventas sin detalles ni ventas confirmadas previamente', function () {
    $tenant = $this->serviceTenant();
    $branch = $this->serviceEstablishment($tenant);
    $warehouse = $this->serviceWarehouse($tenant, $branch);
    $sale = Venta::query()->create([
        'tenant_id' => $tenant->id,
        'establecimiento_id' => $branch->id,
        'almacen_id' => $warehouse->id,
        'folio' => 'V-201',
        'estado' => 'borrador',
    ]);
    $service = app(VentaService::class);

    expect(fn () => $service->confirmar($sale))->toThrow(ValidationException::class);
    $sale->update(['estado' => 'confirmada']);
    expect(fn () => $service->confirmar($sale))->toThrow(ValidationException::class);
});

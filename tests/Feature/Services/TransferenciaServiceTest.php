<?php

use App\Models\Existencia;
use App\Models\Transferencia;
use App\Services\EstablecimientoService;
use App\Services\InventarioService;
use App\Services\TransferenciaService;
use Illuminate\Validation\ValidationException;

it('autoriza, despacha y recibe parcialmente una transferencia entre almacenes', function () {
    $tenant = $this->serviceTenant();
    $sourceBranch = $this->serviceEstablishment($tenant, 'centro_distribucion', 'CD-01');
    $destinationBranch = $this->serviceEstablishment($tenant, 'sucursal', 'SUC-02');
    $sourceWarehouse = $this->serviceWarehouse($tenant, $sourceBranch, 'CD-ALM');
    $destinationWarehouse = $this->serviceWarehouse($tenant, $destinationBranch, 'SUC-ALM');
    $sourceUser = $this->serviceRoleUser($tenant, 'almacen');
    $admin = $this->serviceRoleUser($tenant, 'admin');
    app(EstablecimientoService::class)->asignarUsuario($tenant->id, $sourceUser->id, $sourceBranch->id);
    app(EstablecimientoService::class)->asignarUsuario($tenant->id, $sourceUser->id, $destinationBranch->id);
    $product = $this->serviceProduct($tenant);
    app(InventarioService::class)->registrarEntradaEnAlmacen($tenant->id, $product->id, $sourceWarehouse->id, 10);
    $service = app(TransferenciaService::class);

    $transfer = $service->solicitar($tenant->id, $sourceUser->id, $sourceWarehouse->id, $destinationWarehouse->id, [
        ['producto_id' => $product->id, 'cantidad' => 6],
    ]);

    expect($transfer->estado)->toBe('solicitada')
        ->and(fn () => $service->despachar($transfer->id, $sourceUser->id))->toThrow(ValidationException::class);

    $service->autorizar($transfer->id, $admin->id);
    $service->despachar($transfer->id, $sourceUser->id);
    $detailId = $transfer->detalles->first()->id;

    expect((float) Existencia::query()->where('almacen_id', $sourceWarehouse->id)->sum('cantidad'))->toBe(4.0)
        ->and((float) Existencia::query()->where('almacen_id', $destinationWarehouse->id)->sum('cantidad'))->toBe(0.0)
        ->and($service->recibir($transfer->id, $sourceUser->id, [$detailId => 2])->estado)->toBe('recibida_parcial')
        ->and((float) Existencia::query()->where('almacen_id', $destinationWarehouse->id)->sum('cantidad'))->toBe(2.0)
        ->and($service->recibir($transfer->id, $sourceUser->id, [$detailId => 4])->estado)->toBe('completada')
        ->and((float) Existencia::query()->where('almacen_id', $destinationWarehouse->id)->sum('cantidad'))->toBe(6.0);

    expect(Transferencia::query()->findOrFail($transfer->id)->estado)->toBe('completada');
});

it('impide transferencias entre tenants y recepciones mayores a lo enviado', function () {
    $tenant = $this->serviceTenant();
    $otherTenant = $this->serviceTenant('Farmacia');
    $sourceBranch = $this->serviceEstablishment($tenant, 'centro_distribucion');
    $destinationBranch = $this->serviceEstablishment($tenant);
    $sourceWarehouse = $this->serviceWarehouse($tenant, $sourceBranch);
    $destinationWarehouse = $this->serviceWarehouse($tenant, $destinationBranch);
    $otherBranch = $this->serviceEstablishment($otherTenant);
    $otherWarehouse = $this->serviceWarehouse($otherTenant, $otherBranch);
    $warehouseUser = $this->serviceRoleUser($tenant, 'almacen');
    $admin = $this->serviceRoleUser($tenant, 'admin');
    app(EstablecimientoService::class)->asignarUsuario($tenant->id, $warehouseUser->id, $sourceBranch->id);
    $product = $this->serviceProduct($tenant);
    app(InventarioService::class)->registrarEntradaEnAlmacen($tenant->id, $product->id, $sourceWarehouse->id, 3);
    $service = app(TransferenciaService::class);

    expect(fn () => $service->solicitar($tenant->id, $warehouseUser->id, $sourceWarehouse->id, $otherWarehouse->id, [
        ['producto_id' => $product->id, 'cantidad' => 1],
    ]))->toThrow(ValidationException::class);

    $transfer = $service->solicitar($tenant->id, $warehouseUser->id, $sourceWarehouse->id, $destinationWarehouse->id, [
        ['producto_id' => $product->id, 'cantidad' => 2],
    ]);
    $service->autorizar($transfer->id, $admin->id);
    $service->despachar($transfer->id, $warehouseUser->id);
    $detailId = $transfer->detalles->first()->id;

    expect(fn () => $service->recibir($transfer->id, $warehouseUser->id, [$detailId => 3]))
        ->toThrow(ValidationException::class);
});

<?php

use App\Models\Mantenimiento;
use App\Services\MantenimientoService;
use Illuminate\Validation\ValidationException;

it('mueve la unidad a mantenimiento y la devuelve a disponible al finalizar', function () {
    $tenant = $this->serviceTenant('Electronica');
    $product = $this->serviceProduct($tenant);
    $unit = $this->serviceUnit($tenant, $product, $this->serviceLocation($tenant));
    $maintenance = Mantenimiento::query()->create([
        'tenant_id' => $tenant->id,
        'unidad_inventario_id' => $unit->id,
        'tipo' => 'preventivo',
    ]);
    $service = app(MantenimientoService::class);

    expect($service->iniciar($maintenance->id)->estado)->toBe('en_proceso')
        ->and($unit->fresh()->estado)->toBe('mantenimiento')
        ->and($service->finalizar($maintenance->id)->estado)->toBe('completado')
        ->and($unit->fresh()->estado)->toBe('disponible');
});

it('rechaza iniciar un mantenimiento cuya unidad no está disponible', function () {
    $tenant = $this->serviceTenant('Electronica');
    $unit = $this->serviceUnit($tenant, $this->serviceProduct($tenant), $this->serviceLocation($tenant), ['estado' => 'prestada']);
    $maintenance = Mantenimiento::query()->create(['tenant_id' => $tenant->id, 'unidad_inventario_id' => $unit->id, 'tipo' => 'correctivo']);

    expect(fn () => app(MantenimientoService::class)->iniciar($maintenance->id))->toThrow(ValidationException::class);
});

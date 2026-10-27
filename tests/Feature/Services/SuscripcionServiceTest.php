<?php

use App\Services\SuscripcionService;
use Illuminate\Validation\ValidationException;

it('resuelve la suscripción vigente y permite altas dentro de los límites del plan', function () {
    $tenant = $this->serviceTenant();
    $plan = $this->servicePlan(['limite_usuarios' => 2, 'limite_productos' => 2]);
    $this->serviceSubscription($tenant, $plan);
    $service = app(SuscripcionService::class);

    expect($service->vigente($tenant->id)?->plan_id)->toBe($plan->id)
        ->and($service->validarLimiteUsuarios($tenant->id))->toBeNull()
        ->and($service->validarLimiteProductos($tenant->id))->toBeNull();
});

it('rechaza cuando el tenant no tiene suscripción vigente o agotó su cuota', function () {
    $tenant = $this->serviceTenant();
    $plan = $this->servicePlan(['limite_usuarios' => 1]);
    $this->serviceSubscription($tenant, $plan);
    \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
    $service = app(SuscripcionService::class);

    expect(fn () => $service->validarLimiteUsuarios($tenant->id))->toThrow(ValidationException::class);

    $otherTenant = $this->serviceTenant('Farmacia');
    expect($service->vigente($otherTenant->id))->toBeNull()
        ->and(fn () => $service->validarLimiteProductos($otherTenant->id))->toThrow(ValidationException::class);
});

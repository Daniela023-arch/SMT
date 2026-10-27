<?php

use App\Models\PromocionSuscripcion;
use App\Services\PromocionService;
use Illuminate\Validation\ValidationException;

it('asigna una promoción vigente y trata la segunda asignación como idempotente', function () {
    $tenant = $this->serviceTenant();
    $subscription = $this->serviceSubscription($tenant);
    $promotion = PromocionSuscripcion::query()->create([
        'codigo' => 'BIENVENIDA', 'tipo' => 'porcentaje', 'valor' => 10,
        'vigencia_inicio' => now()->subDay(), 'vigencia_fin' => now()->addDay(),
        'usos_maximos' => 1, 'activo' => true,
    ]);
    $service = app(PromocionService::class);

    $first = $service->asignar($subscription->id, $promotion->codigo);
    $second = $service->asignar($subscription->id, $promotion->codigo);

    expect($first->id)->toBe($second->id)->and($promotion->asignacionesPromocion()->count())->toBe(1);
});

it('rechaza promociones inactivas o fuera de vigencia', function () {
    $tenant = $this->serviceTenant();
    $subscription = $this->serviceSubscription($tenant);
    PromocionSuscripcion::query()->create([
        'codigo' => 'VENCIDA', 'tipo' => 'porcentaje', 'valor' => 10,
        'vigencia_fin' => now()->subDay(), 'activo' => true,
    ]);

    expect(fn () => app(PromocionService::class)->asignar($subscription->id, 'VENCIDA'))
        ->toThrow(ValidationException::class);
});

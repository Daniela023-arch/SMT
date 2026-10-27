<?php

use App\Services\WebhookService;

it('deduplica entregas externas y mantiene el ciclo de procesamiento del webhook', function () {
    $service = app(WebhookService::class);
    $first = $service->recibir('stripe', 'evt_123', 'payment.succeeded', ['id' => 'evt_123']);
    $duplicate = $service->recibir('stripe', 'evt_123', 'payment.succeeded', ['id' => 'evt_123']);

    expect($duplicate->id)->toBe($first->id)
        ->and($service->iniciarProcesamiento($first))->toBeTrue()
        ->and($service->iniciarProcesamiento($first))->toBeFalse();

    $processed = $service->marcarProcesado($first);
    expect($processed->estado)->toBe('procesado')
        ->and($processed->intentos)->toBe(1)
        ->and($service->iniciarProcesamiento($processed))->toBeFalse();
});

it('permite reintentar un webhook fallido y guarda el error', function () {
    $service = app(WebhookService::class);
    $event = $service->recibir('stripe', 'evt_456', 'payment.failed', ['id' => 'evt_456']);

    expect($service->iniciarProcesamiento($event))->toBeTrue()
        ->and($service->marcarFallido($event, 'Timeout')->ultimo_error)->toBe('Timeout')
        ->and($service->iniciarProcesamiento($event->fresh()))->toBeTrue()
        ->and($event->fresh()->intentos)->toBe(2);
});

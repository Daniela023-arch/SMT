<?php

use App\Services\ImportacionCsvService;
use Illuminate\Validation\ValidationException;

it('inicia y finaliza una importación guardando los contadores y errores', function () {
    $tenant = $this->serviceTenant();
    $service = app(ImportacionCsvService::class);
    $import = $service->iniciar($tenant->id, 'imports/productos.csv');

    $result = $service->finalizar($import, 3, 2, [['fila' => 3, 'error' => 'SKU duplicado']]);

    expect($result->estado)->toBe('completada_con_errores')
        ->and($result->filas_totales)->toBe(3)
        ->and($result->filas_exitosas)->toBe(2)
        ->and($result->filas_fallidas)->toBe(1)
        ->and($result->errores)->toHaveCount(1);
});

it('rechaza finalizar con contadores inconsistentes o cerrar dos veces', function () {
    $tenant = $this->serviceTenant();
    $service = app(ImportacionCsvService::class);
    $import = $service->iniciar($tenant->id, 'imports/productos.csv');

    expect(fn () => $service->finalizar($import, 1, 2))->toThrow(ValidationException::class);

    $closed = $service->finalizar($import, 1, 1);
    expect(fn () => $service->finalizar($closed, 1, 1))->toThrow(ValidationException::class);
});

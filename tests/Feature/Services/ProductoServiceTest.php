<?php

use App\Models\Categoria;
use App\Services\ProductoService;
use Illuminate\Validation\ValidationException;

it('crea un producto en el tenant con suscripción vigente', function () {
    $tenant = $this->serviceTenant();
    $this->serviceSubscription($tenant);
    $category = Categoria::query()->create(['tenant_id' => $tenant->id, 'nombre' => 'General']);

    $product = app(ProductoService::class)->crear($tenant->id, [
        'sku' => 'SKU-NEW',
        'nombre' => 'Cuaderno',
        'categoria_id' => $category->id,
    ]);

    expect($product->tenant_id)->toBe($tenant->id)
        ->and($product->categoria_id)->toBe($category->id);
});

it('rechaza productos sin suscripción, con categoría ajena o SKU duplicado', function () {
    $tenant = $this->serviceTenant();
    $otherTenant = $this->serviceTenant('Farmacia');
    $this->serviceSubscription($tenant);
    $foreignCategory = Categoria::query()->create(['tenant_id' => $otherTenant->id, 'nombre' => 'Ajena']);
    $service = app(ProductoService::class);
    $data = ['sku' => 'SKU-UNICO', 'nombre' => 'Producto'];
    $service->crear($tenant->id, $data);

    expect(fn () => $service->crear($tenant->id, array_merge($data, ['sku' => 'SKU-OTRO', 'categoria_id' => $foreignCategory->id])))
        ->toThrow(ValidationException::class)
        ->and(fn () => $service->crear($tenant->id, $data))->toThrow(ValidationException::class);
});

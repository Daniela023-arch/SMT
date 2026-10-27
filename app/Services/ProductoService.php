<?php
namespace App\Services;

use App\Models\{Categoria,Producto,Tenant};
use Illuminate\Validation\ValidationException;

class ProductoService
{
    public function __construct(private readonly SuscripcionService $suscripciones) {}

    public function crear(int $tenantId, array $datos): Producto
    {
        Tenant::query()->findOrFail($tenantId);
        $this->suscripciones->validarLimiteProductos($tenantId);
        if (!empty($datos['categoria_id']) && !Categoria::query()->where('tenant_id', $tenantId)->whereKey($datos['categoria_id'])->exists()) {
            throw ValidationException::withMessages(['categoria_id' => 'La categoría no pertenece al tenant.']);
        }
        if (empty($datos['sku']) || empty($datos['nombre'])) {
            throw ValidationException::withMessages(['producto' => 'SKU y nombre son obligatorios.']);
        }
        if (Producto::query()->withTrashed()->where('tenant_id', $tenantId)->where('sku', $datos['sku'])->exists()) {
            throw ValidationException::withMessages(['sku' => 'El SKU ya existe en este tenant.']);
        }
        return Producto::query()->create(array_merge($datos, ['tenant_id' => $tenantId]));
    }
}

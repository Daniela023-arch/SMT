<?php

namespace App\Services;

use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

class SuscripcionService
{
    public function vigente(int $tenantId): ?SuscripcionTenant
    {
        return SuscripcionTenant::query()
            ->with('plan')
            ->where('tenant_id', $tenantId)
            ->whereIn('estado', ['activa', 'activo', 'prueba'])
            ->where('inicio_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('fin_at')->orWhere('fin_at', '>=', now()))
            ->whereHas('plan', fn ($query) => $query->where('activo', true))
            ->latest('inicio_at')
            ->first();
    }

    public function validarLimiteUsuarios(int $tenantId, int $usuariosNuevos = 1): void
    {
        $suscripcion = $this->requerirVigente($tenantId);
        $limite = $suscripcion->plan?->limite_usuarios;

        if ($limite !== null && Tenant::query()->findOrFail($tenantId)->usuarios()->count() + $usuariosNuevos > $limite) {
            throw ValidationException::withMessages(['plan' => 'Se alcanzó el límite de usuarios del plan.']);
        }
    }

    public function validarLimiteProductos(int $tenantId, int $productosNuevos = 1): void
    {
        $suscripcion = $this->requerirVigente($tenantId);
        $limite = $suscripcion->plan?->limite_productos;

        if ($limite !== null && Tenant::query()->findOrFail($tenantId)->productos()->count() + $productosNuevos > $limite) {
            throw ValidationException::withMessages(['plan' => 'Se alcanzó el límite de productos del plan.']);
        }
    }

    private function requerirVigente(int $tenantId): SuscripcionTenant
    {
        return $this->vigente($tenantId)
            ?? throw ValidationException::withMessages(['suscripcion' => 'El tenant no tiene un plan activo.']);
    }
}

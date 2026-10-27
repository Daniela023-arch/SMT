<?php

namespace App\Services;

use App\Models\Modulo;
use App\Models\ModuloTenant;
use Illuminate\Validation\ValidationException;

class ModuloTenantService
{
    public function habilitar(int $tenantId, string $claveModulo, bool $habilitado = true): ModuloTenant
    {
        $modulo = Modulo::query()->where('clave', $claveModulo)->where('activo', true)->first();

        if ($modulo === null) {
            throw ValidationException::withMessages(['modulo' => 'El módulo no existe o está desactivado globalmente.']);
        }

        return ModuloTenant::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'modulo_id' => $modulo->getKey()],
            ['habilitado' => $habilitado],
        );
    }
}

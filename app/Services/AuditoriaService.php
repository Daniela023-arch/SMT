<?php

namespace App\Services;

use App\Models\Auditoria;

class AuditoriaService
{
    public function registrar(
        int $usuarioId,
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        ?int $tenantId = null,
        ?array $anteriores = null,
        ?array $nuevos = null,
    ): Auditoria {
        return Auditoria::query()->create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'tenant_id' => $tenantId,
            'datos_anteriores' => $anteriores,
            'datos_nuevos' => $nuevos,
            'ip' => request()->ip(),
            'agente_usuario' => request()->userAgent(),
        ]);
    }
}

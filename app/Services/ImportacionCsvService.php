<?php

namespace App\Services;

use App\Models\ImportacionCsv;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ImportacionCsvService
{
    public function iniciar(int $tenantId, string $archivo, ?int $usuarioId = null): ImportacionCsv
    {
        return ImportacionCsv::query()->create([
            'tenant_id' => $tenantId,
            'usuario_id' => $usuarioId,
            'archivo' => $archivo,
            'estado' => 'procesando',
        ]);
    }

    /** @param array<int, array<string, mixed>> $errores */
    public function finalizar(ImportacionCsv $importacion, int $total, int $exitosas, array $errores = []): ImportacionCsv
    {
        if ($total < 0 || $exitosas < 0 || $exitosas > $total || count($errores) > $total - $exitosas) {
            throw ValidationException::withMessages(['importacion' => 'Los totales de filas de la importación no son válidos.']);
        }

        if ($importacion->estado !== 'procesando' && $importacion->estado !== 'pendiente') {
            throw ValidationException::withMessages(['importacion' => 'La importación ya fue cerrada.']);
        }

        $fallidas = $total - $exitosas;
        $importacion->fill([
            'estado' => $fallidas === 0 ? 'completada' : ($exitosas === 0 ? 'fallida' : 'completada_con_errores'),
            'filas_totales' => $total,
            'filas_exitosas' => $exitosas,
            'filas_fallidas' => $fallidas,
            'errores' => Arr::take($errores, 1000),
        ])->save();

        return $importacion->refresh();
    }
}

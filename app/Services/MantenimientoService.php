<?php
namespace App\Services;

use App\Models\{Mantenimiento,UnidadInventario};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MantenimientoService
{
    public function iniciar(int $mantenimientoId): Mantenimiento
    {
        return DB::transaction(function () use ($mantenimientoId) {
            $m = Mantenimiento::query()->lockForUpdate()->findOrFail($mantenimientoId);
            $unidad = UnidadInventario::query()->where('tenant_id', $m->tenant_id)->lockForUpdate()->findOrFail($m->unidad_inventario_id);
            if ($m->estado !== 'programado' || $unidad->estado !== 'disponible') {
                throw ValidationException::withMessages(['mantenimiento' => 'Solo pueden iniciar mantenimientos programados de unidades disponibles.']);
            }
            $unidad->update(['estado' => 'mantenimiento']);
            $m->update(['estado' => 'en_proceso']);
            return $m->refresh();
        });
    }

    public function finalizar(int $mantenimientoId): Mantenimiento
    {
        return DB::transaction(function () use ($mantenimientoId) {
            $m = Mantenimiento::query()->lockForUpdate()->findOrFail($mantenimientoId);
            $unidad = UnidadInventario::query()->where('tenant_id', $m->tenant_id)->lockForUpdate()->findOrFail($m->unidad_inventario_id);
            if ($m->estado !== 'en_proceso' || $unidad->estado !== 'mantenimiento') {
                throw ValidationException::withMessages(['mantenimiento' => 'El mantenimiento no está en proceso.']);
            }
            $unidad->update(['estado' => 'disponible']);
            $m->update(['estado' => 'completado', 'fecha_realizada' => now()]);
            return $m->refresh();
        });
    }
}

<?php
namespace App\Services;

use App\Models\{Baja,DetalleBaja,UnidadInventario};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BajaService
{
    public function __construct(private readonly InventarioService $inventario) {}

    // La ubicación de origen es explícita porque detalles_baja no la contiene.
    public function aprobar(int $bajaId, int $ubicacionId): Baja
    {
        return DB::transaction(function () use ($bajaId, $ubicacionId) {
            $baja = Baja::query()->lockForUpdate()->findOrFail($bajaId);
            if ($baja->estado !== 'borrador') {
                throw ValidationException::withMessages(['baja' => 'La baja ya se procesó.']);
            }
            $detalles = DetalleBaja::query()->where('baja_id', $bajaId)->lockForUpdate()->get();
            if ($detalles->isEmpty()) throw ValidationException::withMessages(['baja' => 'La baja necesita detalles.']);
            foreach ($detalles as $detalle) {
                if ((int) $detalle->tenant_id !== (int) $baja->tenant_id) {
                    throw ValidationException::withMessages(['baja' => 'Hay detalles de otro tenant.']);
                }
                if ($detalle->unidad_inventario_id !== null) {
                    if ((float) $detalle->cantidad !== 1.0) throw ValidationException::withMessages(['cantidad' => 'Una unidad seriada requiere cantidad 1.']);
                    $unidad = UnidadInventario::query()->where('tenant_id', $baja->tenant_id)->lockForUpdate()->findOrFail($detalle->unidad_inventario_id);
                    if ((int) $unidad->producto_id !== (int) $detalle->producto_id || $unidad->estado !== 'disponible') {
                        throw ValidationException::withMessages(['unidad' => 'La unidad no está disponible o no coincide con el producto.']);
                    }
                }
                $this->inventario->registrarSalida((int) $baja->tenant_id, (int) $detalle->producto_id, $ubicacionId,
                    (float) $detalle->cantidad, $detalle->lote_id, $baja->usuario_id,
                    'Baja '.$baja->folio, Baja::class, (int) $baja->getKey(), 'salida_baja', $detalle->unidad_inventario_id);
                if (isset($unidad)) { $unidad->update(['estado' => 'baja']); unset($unidad); }
            }
            $baja->update(['estado' => 'aprobada', 'aprobada_at' => now()]);
            return $baja->refresh();
        });
    }
}

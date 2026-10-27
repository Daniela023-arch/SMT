<?php

namespace App\Services;

use App\Models\{Venta,UnidadInventario};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function confirmar(Venta $venta, int $ubicacionId): Venta
    {
        return DB::transaction(function () use ($venta, $ubicacionId): Venta {
            $venta = Venta::query()->whereKey($venta->getKey())->lockForUpdate()->firstOrFail();

            if ($venta->estado !== 'borrador') {
                throw ValidationException::withMessages(['venta' => 'Solo se pueden confirmar ventas en borrador.']);
            }

            $detalles = $venta->detallesVenta()->lockForUpdate()->get();

            if ($detalles->isEmpty()) {
                throw ValidationException::withMessages(['venta' => 'Agrega al menos un producto antes de confirmar la venta.']);
            }

            foreach ($detalles as $detalle) {
                if ((int) $detalle->tenant_id !== (int) $venta->tenant_id) {
                    throw ValidationException::withMessages(['venta' => 'La venta contiene detalles de otro tenant.']);
                }

                $this->inventario->registrarSalida(
                    tenantId: (int) $venta->tenant_id,
                    productoId: (int) $detalle->producto_id,
                    ubicacionId: $ubicacionId,
                    cantidad: (float) $detalle->cantidad,
                    loteId: $detalle->lote_id === null ? null : (int) $detalle->lote_id,
                    usuarioId: $venta->usuario_id === null ? null : (int) $venta->usuario_id,
                    motivo: 'Salida por venta '.$venta->folio,
                    referenciaTipo: Venta::class,
                    referenciaId: (int) $venta->getKey(),
                    tipo: 'salida_venta',
                    unidadInventarioId: $detalle->unidad_inventario_id === null ? null : (int) $detalle->unidad_inventario_id,
                );
                if ($detalle->unidad_inventario_id !== null) {
                    $unidad = UnidadInventario::query()->where('tenant_id', $venta->tenant_id)->lockForUpdate()->findOrFail($detalle->unidad_inventario_id);
                    $unidad->update(['estado' => 'vendida']);
                }
            }

            $venta->estado = 'confirmada';
            $venta->save();

            return $venta->refresh();
        });
    }
}

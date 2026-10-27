<?php

namespace App\Services;

use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\UnidadInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrestamoService
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function prestarUnidad(Prestamo $prestamo, int $unidadId): DetallePrestamo
    {
        return DB::transaction(function () use ($prestamo, $unidadId): DetallePrestamo {
            $prestamo = Prestamo::query()->whereKey($prestamo->getKey())->lockForUpdate()->firstOrFail();
            $unidad = UnidadInventario::query()->where('tenant_id', $prestamo->tenant_id)->lockForUpdate()->find($unidadId);

            if ($unidad === null || $unidad->estado !== 'disponible' || $unidad->ubicacion_id === null) {
                throw ValidationException::withMessages(['unidad_inventario_id' => 'La unidad debe pertenecer al tenant, estar disponible y tener ubicación.']);
            }

            $detalle = DetallePrestamo::query()->create([
                'tenant_id' => $prestamo->tenant_id,
                'prestamo_id' => $prestamo->getKey(),
                'unidad_inventario_id' => $unidad->getKey(),
                'estado' => 'prestado',
            ]);

            $this->inventario->registrarSalida(
                tenantId: (int) $prestamo->tenant_id,
                productoId: (int) $unidad->producto_id,
                ubicacionId: (int) $unidad->ubicacion_id,
                cantidad: 1,
                loteId: $unidad->lote_id === null ? null : (int) $unidad->lote_id,
                usuarioId: $prestamo->usuario_id === null ? null : (int) $prestamo->usuario_id,
                motivo: 'Préstamo '.$prestamo->folio,
                referenciaTipo: Prestamo::class,
                referenciaId: (int) $prestamo->getKey(),
                tipo: 'salida_prestamo',
                unidadInventarioId: (int) $unidad->getKey(),
            );

            $unidad->estado = 'prestada';
            $unidad->save();

            return $detalle;
        });
    }

    public function recibirUnidad(DetallePrestamo $detalle): DetallePrestamo
    {
        return DB::transaction(function () use ($detalle): DetallePrestamo {
            $detalle = DetallePrestamo::query()->whereKey($detalle->getKey())->lockForUpdate()->firstOrFail();

            if ($detalle->estado !== 'prestado' || $detalle->devuelto_at !== null) {
                throw ValidationException::withMessages(['prestamo' => 'Esta unidad ya fue devuelta o no está prestada.']);
            }

            $unidad = UnidadInventario::query()->where('tenant_id', $detalle->tenant_id)->lockForUpdate()->findOrFail($detalle->unidad_inventario_id);

            if ($unidad->ubicacion_id === null) {
                throw ValidationException::withMessages(['unidad_inventario_id' => 'Asigna una ubicación de devolución a la unidad.']);
            }

            $this->inventario->registrarEntrada(
                tenantId: (int) $detalle->tenant_id,
                productoId: (int) $unidad->producto_id,
                ubicacionId: (int) $unidad->ubicacion_id,
                cantidad: 1,
                loteId: $unidad->lote_id === null ? null : (int) $unidad->lote_id,
                usuarioId: $detalle->prestamo?->usuario_id === null ? null : (int) $detalle->prestamo->usuario_id,
                motivo: 'Devolución de préstamo '.$detalle->prestamo?->folio,
                referenciaTipo: Prestamo::class,
                referenciaId: (int) $detalle->prestamo_id,
                tipo: 'entrada_devolucion',
                unidadInventarioId: (int) $unidad->getKey(),
            );

            $unidad->estado = 'disponible';
            $unidad->save();
            $detalle->estado = 'devuelto';
            $detalle->devuelto_at = now();
            $detalle->save();

            return $detalle->refresh();
        });
    }
}

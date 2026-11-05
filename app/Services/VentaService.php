<?php

namespace App\Services;

use App\Models\{Venta,UnidadInventario};
use App\Models\Almacen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function confirmar(Venta $venta): Venta
    {
        return DB::transaction(function () use ($venta): Venta {
            $venta = Venta::query()->whereKey($venta->getKey())->lockForUpdate()->firstOrFail();

            if ($venta->estado !== 'borrador') {
                throw ValidationException::withMessages(['venta' => 'Solo se pueden confirmar ventas en borrador.']);
            }

            $detalles = $venta->detallesVenta()->lockForUpdate()->get();

            if ($detalles->isEmpty()) {
                throw ValidationException::withMessages(['venta' => 'Agrega al menos un producto antes de confirmar la venta.']);
            }

            if ($venta->establecimiento_id === null || $venta->almacen_id === null) {
                throw ValidationException::withMessages(['venta' => 'La venta debe indicar la sucursal y el almacén desde donde se surtirá.']);
            }

            $almacen = Almacen::query()
                ->where('tenant_id', $venta->tenant_id)
                ->where('establecimiento_id', $venta->establecimiento_id)
                ->where('activo', true)
                ->find($venta->almacen_id);

            if ($almacen === null) {
                throw ValidationException::withMessages(['almacen_id' => 'El almacén no pertenece a la sucursal de la venta.']);
            }

            foreach ($detalles as $detalle) {
                if ((int) $detalle->tenant_id !== (int) $venta->tenant_id) {
                    throw ValidationException::withMessages(['venta' => 'La venta contiene detalles de otro tenant.']);
                }

                if ($detalle->unidad_inventario_id !== null) {
                    $unidad = UnidadInventario::query()
                        ->where('tenant_id', $venta->tenant_id)
                        ->lockForUpdate()
                        ->findOrFail($detalle->unidad_inventario_id);

                    if ((int) $unidad->producto_id !== (int) $detalle->producto_id || $unidad->estado !== 'disponible') {
                        throw ValidationException::withMessages(['unidad_inventario_id' => 'La unidad no está disponible o no coincide con el producto.']);
                    }

                    $this->inventario->registrarSalida(
                        (int) $venta->tenant_id,
                        (int) $detalle->producto_id,
                        (int) $unidad->ubicacion_id,
                        (float) $detalle->cantidad,
                        $detalle->lote_id === null ? null : (int) $detalle->lote_id,
                        $venta->usuario_id === null ? null : (int) $venta->usuario_id,
                        'Salida por venta '.$venta->folio,
                        Venta::class,
                        (int) $venta->getKey(),
                        'salida_venta',
                        (int) $unidad->getKey(),
                        (int) $almacen->getKey(),
                    );

                    $unidad->update(['estado' => 'vendida']);
                } else {
                    $this->inventario->registrarSalidaDeAlmacen(
                        (int) $venta->tenant_id,
                        (int) $detalle->producto_id,
                        (int) $almacen->getKey(),
                        (float) $detalle->cantidad,
                        null,
                        $detalle->lote_id === null ? null : (int) $detalle->lote_id,
                        $venta->usuario_id === null ? null : (int) $venta->usuario_id,
                        'Salida por venta '.$venta->folio,
                        Venta::class,
                        (int) $venta->getKey(),
                        'salida_venta',
                    );
                }
            }

            $venta->estado = 'confirmada';
            $venta->save();

            return $venta->refresh();
        });
    }
}

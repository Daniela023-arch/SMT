<?php

namespace App\Services;

use App\Models\ConfiguracionInventario;
use App\Models\Existencia;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\Ubicacion;
use App\Models\UnidadInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    public function registrarEntrada(
        int $tenantId,
        int $productoId,
        int $ubicacionId,
        float $cantidad,
        ?int $loteId = null,
        ?int $usuarioId = null,
        ?string $motivo = null,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
        string $tipo = 'entrada',
        ?int $unidadInventarioId = null,
    ): StockMovimiento {
        return $this->registrarCambio(
            $tenantId, $productoId, $cantidad, $usuarioId, $loteId,
            null, $ubicacionId, $motivo, $referenciaTipo, $referenciaId, $tipo, $unidadInventarioId,
        );
    }

    public function registrarSalida(
        int $tenantId,
        int $productoId,
        int $ubicacionId,
        float $cantidad,
        ?int $loteId = null,
        ?int $usuarioId = null,
        ?string $motivo = null,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
        string $tipo = 'salida',
        ?int $unidadInventarioId = null,
    ): StockMovimiento {
        return $this->registrarCambio(
            $tenantId, $productoId, $cantidad, $usuarioId, $loteId,
            $ubicacionId, null, $motivo, $referenciaTipo, $referenciaId, $tipo,
            $unidadInventarioId,
        );
    }

    public function transferir(
        int $tenantId,
        int $productoId,
        int $ubicacionOrigenId,
        int $ubicacionDestinoId,
        float $cantidad,
        ?int $loteId = null,
        ?int $usuarioId = null,
        ?string $motivo = null,
    ): StockMovimiento {
        if ($ubicacionOrigenId === $ubicacionDestinoId) {
            throw ValidationException::withMessages(['ubicacion_destino_id' => 'El origen y destino deben ser distintos.']);
        }

        return $this->registrarCambio(
            $tenantId, $productoId, $cantidad, $usuarioId, $loteId,
            $ubicacionOrigenId, $ubicacionDestinoId, $motivo, null, null, 'transferencia',
        );
    }

    private function registrarCambio(
        int $tenantId,
        int $productoId,
        float $cantidad,
        ?int $usuarioId,
        ?int $loteId,
        ?int $ubicacionOrigenId,
        ?int $ubicacionDestinoId,
        ?string $motivo,
        ?string $referenciaTipo,
        ?int $referenciaId,
        string $tipo,
        ?int $unidadInventarioId = null,
    ): StockMovimiento {
        if ($cantidad <= 0 || abs($cantidad - round($cantidad, 3)) > 0.000001) {
            throw ValidationException::withMessages(['cantidad' => 'La cantidad debe ser positiva y tener como máximo tres decimales.']);
        }

        return DB::transaction(function () use (
            $tenantId, $productoId, $cantidad, $usuarioId, $loteId, $ubicacionOrigenId,
            $ubicacionDestinoId, $motivo, $referenciaTipo, $referenciaId, $tipo, $unidadInventarioId,
        ): StockMovimiento {
            // Lock the product row so concurrent stock changes for it are serialized.
            $producto = Producto::query()->where('tenant_id', $tenantId)->lockForUpdate()->find($productoId);

            if ($producto === null) {
                throw ValidationException::withMessages(['producto_id' => 'El producto no pertenece a este tenant.']);
            }

            foreach (array_filter([$ubicacionOrigenId, $ubicacionDestinoId]) as $ubicacionId) {
                if (! Ubicacion::query()->where('tenant_id', $tenantId)->whereKey($ubicacionId)->exists()) {
                    throw ValidationException::withMessages(['ubicacion_id' => 'La ubicación no pertenece a este tenant.']);
                }
            }

            if ($loteId !== null && ! Lote::query()->where('tenant_id', $tenantId)->where('producto_id', $productoId)->whereKey($loteId)->exists()) {
                throw ValidationException::withMessages(['lote_id' => 'El lote no pertenece a este producto y tenant.']);
            }

            if ($unidadInventarioId !== null) {
                $unidad = UnidadInventario::query()
                    ->where('tenant_id', $tenantId)
                    ->where('producto_id', $productoId)
                    ->whereKey($unidadInventarioId)
                    ->first();

                if ($unidad === null || ($loteId !== null && (int) $unidad->lote_id !== $loteId)) {
                    throw ValidationException::withMessages(['unidad_inventario_id' => 'La unidad no pertenece a este producto, lote y tenant.']);
                }

                if ($cantidad != 1.0) {
                    throw ValidationException::withMessages(['cantidad' => 'Una unidad identificada individualmente solo admite cantidad 1.']);
                }

                if ($ubicacionOrigenId !== null && $unidad->estado !== 'disponible') {
                    throw ValidationException::withMessages(['unidad_inventario_id' => 'La unidad no está disponible para salida.']);
                }

                if ($ubicacionOrigenId !== null && (int) $unidad->ubicacion_id !== $ubicacionOrigenId) {
                    throw ValidationException::withMessages(['unidad_inventario_id' => 'La unidad no está en la ubicación de origen.']);
                }
            }

            $permitirNegativo = ConfiguracionInventario::query()
                ->where('tenant_id', $tenantId)
                ->value('permitir_stock_negativo') ?? false;

            if ($ubicacionOrigenId !== null) {
                $origen = $this->existencia($tenantId, $productoId, $ubicacionOrigenId, $loteId);

                if (! $permitirNegativo && (float) $origen->cantidad < $cantidad) {
                    throw ValidationException::withMessages(['cantidad' => 'No hay existencias suficientes en la ubicación de origen.']);
                }

                $origen->cantidad = round((float) $origen->cantidad - $cantidad, 3);
                $origen->save();
            }

            if ($ubicacionDestinoId !== null) {
                $destino = $this->existencia($tenantId, $productoId, $ubicacionDestinoId, $loteId);
                $destino->cantidad = round((float) $destino->cantidad + $cantidad, 3);
                $destino->save();
            }

            return StockMovimiento::query()->create([
                'tenant_id' => $tenantId,
                'producto_id' => $productoId,
                'lote_id' => $loteId,
                'unidad_inventario_id' => $unidadInventarioId,
                'ubicacion_origen_id' => $ubicacionOrigenId,
                'ubicacion_destino_id' => $ubicacionDestinoId,
                'usuario_id' => $usuarioId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
                'motivo' => $motivo,
            ]);
        });
    }

    private function existencia(int $tenantId, int $productoId, int $ubicacionId, ?int $loteId): Existencia
    {
        $query = Existencia::query()
            ->where('tenant_id', $tenantId)
            ->where('producto_id', $productoId)
            ->where('ubicacion_id', $ubicacionId);

        $loteId === null ? $query->whereNull('lote_id') : $query->where('lote_id', $loteId);

        return $query->first() ?? Existencia::query()->create([
            'tenant_id' => $tenantId,
            'producto_id' => $productoId,
            'ubicacion_id' => $ubicacionId,
            'lote_id' => $loteId,
            'cantidad' => 0,
        ]);
    }
}

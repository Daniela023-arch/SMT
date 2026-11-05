<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Establecimiento;
use App\Models\Producto;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferenciaService
{
    public function __construct(private readonly InventarioService $inventario) {}

    /** @param array<int, array{producto_id:int, cantidad:float, lote_id?:int|null, ubicacion_origen_id?:int|null, ubicacion_destino_id?:int|null}> $lineas */
    public function solicitar(int $tenantId, int $usuarioId, int $almacenOrigenId, int $almacenDestinoId, array $lineas, ?string $motivo = null): Transferencia
    {
        return DB::transaction(function () use ($tenantId, $usuarioId, $almacenOrigenId, $almacenDestinoId, $lineas, $motivo): Transferencia {
            if ($almacenOrigenId === $almacenDestinoId || $lineas === []) {
                throw ValidationException::withMessages(['transferencia' => 'Indica almacenes distintos y al menos un producto.']);
            }

            $origen = $this->almacenActivo($tenantId, $almacenOrigenId);
            $destino = $this->almacenActivo($tenantId, $almacenDestinoId);
            $this->validarUsuario($usuarioId, $tenantId, ['admin', 'almacen']);
            $this->validarAccesoEstablecimiento($usuarioId, $tenantId, (int) $origen->establecimiento_id);

            $transferencia = Transferencia::query()->create([
                'tenant_id' => $tenantId,
                'establecimiento_origen_id' => $origen->establecimiento_id,
                'establecimiento_destino_id' => $destino->establecimiento_id,
                'almacen_origen_id' => $origen->getKey(),
                'almacen_destino_id' => $destino->getKey(),
                'solicitante_id' => $usuarioId,
                'folio' => $this->folio($tenantId),
                'estado' => 'solicitada',
                'motivo' => $motivo,
                'solicitada_at' => now(),
            ]);

            foreach ($lineas as $linea) {
                $productoId = (int) ($linea['producto_id'] ?? 0);
                $cantidad = (float) ($linea['cantidad'] ?? 0);
                $loteId = isset($linea['lote_id']) ? (int) $linea['lote_id'] : null;

                if ($cantidad <= 0 || abs($cantidad - round($cantidad, 4)) > 0.000001) {
                    throw ValidationException::withMessages(['cantidad' => 'Cada cantidad debe ser positiva y tener máximo cuatro decimales.']);
                }

                if (! Producto::query()->where('tenant_id', $tenantId)->whereKey($productoId)->exists()) {
                    throw ValidationException::withMessages(['producto_id' => 'Todos los productos deben pertenecer al tenant.']);
                }

                $ubicacionOrigenId = isset($linea['ubicacion_origen_id']) ? (int) $linea['ubicacion_origen_id'] : null;
                $ubicacionDestinoId = isset($linea['ubicacion_destino_id']) ? (int) $linea['ubicacion_destino_id'] : null;

                if ($ubicacionOrigenId !== null) {
                    $this->validarUbicacion($tenantId, $almacenOrigenId, $ubicacionOrigenId);
                }

                if ($ubicacionDestinoId !== null) {
                    $this->validarUbicacion($tenantId, $almacenDestinoId, $ubicacionDestinoId);
                } else {
                    $ubicacionDestinoId = $this->ubicacionPrincipal($tenantId, $almacenDestinoId);
                }

                TransferenciaDetalle::query()->create([
                    'tenant_id' => $tenantId,
                    'transferencia_id' => $transferencia->getKey(),
                    'producto_id' => $productoId,
                    'lote_id' => $loteId,
                    'ubicacion_origen_id' => $ubicacionOrigenId,
                    'ubicacion_destino_id' => $ubicacionDestinoId,
                    'cantidad_solicitada' => $cantidad,
                ]);
            }

            return $transferencia->load('detalles');
        });
    }

    public function autorizar(int $transferenciaId, int $administradorId): Transferencia
    {
        return $this->cambiarEstadoSolicitado($transferenciaId, $administradorId, 'aprobada');
    }

    public function rechazar(int $transferenciaId, int $administradorId, string $motivo): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $administradorId, $motivo): Transferencia {
            $transferencia = Transferencia::query()->lockForUpdate()->findOrFail($transferenciaId);
            $this->validarUsuario($administradorId, (int) $transferencia->tenant_id, ['admin']);

            if ($transferencia->estado !== 'solicitada') {
                throw ValidationException::withMessages(['transferencia' => 'Solo se pueden rechazar transferencias solicitadas.']);
            }

            $transferencia->update([
                'estado' => 'rechazada',
                'autorizador_id' => $administradorId,
                'autorizada_at' => now(),
                'motivo_rechazo' => $motivo,
            ]);

            return $transferencia->refresh();
        });
    }

    public function despachar(int $transferenciaId, int $usuarioId): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $usuarioId): Transferencia {
            $transferencia = Transferencia::query()->with('detalles')->lockForUpdate()->findOrFail($transferenciaId);

            if ($transferencia->estado !== 'aprobada') {
                throw ValidationException::withMessages(['transferencia' => 'Solo se pueden despachar transferencias aprobadas.']);
            }

            $this->validarUsuario($usuarioId, (int) $transferencia->tenant_id, ['admin', 'almacen']);
            $this->validarAccesoEstablecimiento($usuarioId, (int) $transferencia->tenant_id, (int) $transferencia->establecimiento_origen_id);

            foreach ($transferencia->detalles as $detalle) {
                $this->inventario->registrarSalidaDeAlmacen(
                    (int) $transferencia->tenant_id,
                    (int) $detalle->producto_id,
                    (int) $transferencia->almacen_origen_id,
                    (float) $detalle->cantidad_solicitada,
                    $detalle->ubicacion_origen_id === null ? null : (int) $detalle->ubicacion_origen_id,
                    $detalle->lote_id === null ? null : (int) $detalle->lote_id,
                    $usuarioId,
                    'Salida de transferencia '.$transferencia->folio,
                    Transferencia::class,
                    (int) $transferencia->getKey(),
                    'transferencia_salida',
                );

                $detalle->update(['cantidad_enviada' => $detalle->cantidad_solicitada]);
            }

            $transferencia->update(['estado' => 'en_transito', 'en_transito_at' => now()]);

            return $transferencia->refresh()->load('detalles');
        });
    }

    /** @param array<int, numeric> $cantidadesRecibidas Map of transfer detail ID to quantity received in this delivery. */
    public function recibir(int $transferenciaId, int $usuarioId, array $cantidadesRecibidas): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $usuarioId, $cantidadesRecibidas): Transferencia {
            $transferencia = Transferencia::query()->with('detalles')->lockForUpdate()->findOrFail($transferenciaId);

            if (! in_array($transferencia->estado, ['en_transito', 'recibida_parcial'], true) || $cantidadesRecibidas === []) {
                throw ValidationException::withMessages(['transferencia' => 'La transferencia no está en tránsito o no hay cantidades por recibir.']);
            }

            $this->validarUsuario($usuarioId, (int) $transferencia->tenant_id, ['admin', 'almacen']);
            $this->validarAccesoEstablecimiento($usuarioId, (int) $transferencia->tenant_id, (int) $transferencia->establecimiento_destino_id);

            foreach ($cantidadesRecibidas as $detalleId => $cantidad) {
                $detalle = $transferencia->detalles->firstWhere('id', (int) $detalleId);
                $cantidad = is_numeric($cantidad) ? round((float) $cantidad, 4) : 0;

                if ($detalle === null || $cantidad <= 0 || $cantidad > (float) $detalle->cantidad_enviada - (float) $detalle->cantidad_recibida) {
                    throw ValidationException::withMessages(['cantidad' => 'La recepción excede lo enviado o el detalle no corresponde a la transferencia.']);
                }

                $this->inventario->registrarEntrada(
                    (int) $transferencia->tenant_id,
                    (int) $detalle->producto_id,
                    (int) $detalle->ubicacion_destino_id,
                    $cantidad,
                    $detalle->lote_id === null ? null : (int) $detalle->lote_id,
                    $usuarioId,
                    'Recepción de transferencia '.$transferencia->folio,
                    Transferencia::class,
                    (int) $transferencia->getKey(),
                    'transferencia_entrada',
                    null,
                    (int) $transferencia->almacen_destino_id,
                );

                $detalle->cantidad_recibida = round((float) $detalle->cantidad_recibida + $cantidad, 4);
                $detalle->save();
            }

            $transferencia->load('detalles');
            $completa = $transferencia->detalles->every(
                fn (TransferenciaDetalle $detalle): bool => (float) $detalle->cantidad_recibida >= (float) $detalle->cantidad_enviada,
            );

            $transferencia->update([
                'estado' => $completa ? 'completada' : 'recibida_parcial',
                'recibida_at' => $completa ? now() : null,
            ]);

            return $transferencia->refresh()->load('detalles');
        });
    }

    public function cancelar(int $transferenciaId, int $usuarioId): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $usuarioId): Transferencia {
            $transferencia = Transferencia::query()->lockForUpdate()->findOrFail($transferenciaId);
            $this->validarUsuario($usuarioId, (int) $transferencia->tenant_id, ['admin']);

            if (! in_array($transferencia->estado, ['solicitada', 'aprobada'], true)) {
                throw ValidationException::withMessages(['transferencia' => 'No se puede cancelar una transferencia que ya fue despachada.']);
            }

            $transferencia->update(['estado' => 'cancelada', 'cancelada_at' => now()]);

            return $transferencia->refresh();
        });
    }

    private function cambiarEstadoSolicitado(int $transferenciaId, int $administradorId, string $estado): Transferencia
    {
        return DB::transaction(function () use ($transferenciaId, $administradorId, $estado): Transferencia {
            $transferencia = Transferencia::query()->lockForUpdate()->findOrFail($transferenciaId);
            $this->validarUsuario($administradorId, (int) $transferencia->tenant_id, ['admin']);

            if ($transferencia->estado !== 'solicitada') {
                throw ValidationException::withMessages(['transferencia' => 'Solo se pueden autorizar transferencias solicitadas.']);
            }

            $transferencia->update([
                'estado' => $estado,
                'autorizador_id' => $administradorId,
                'autorizada_at' => now(),
            ]);

            return $transferencia->refresh();
        });
    }

    private function validarUsuario(int $usuarioId, int $tenantId, array $roles): User
    {
        $usuario = User::query()->where('tenant_id', $tenantId)->where('activo', true)->find($usuarioId);

        $tieneRol = $usuario !== null && $usuario->roles()
            ->whereIn('roles.slug', $roles)
            ->where('usuario_rol.tenant_id', $tenantId)
            ->exists();

        if (! $tieneRol) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no tiene el rol requerido en este tenant.']);
        }

        return $usuario;
    }

    private function validarAccesoEstablecimiento(int $usuarioId, int $tenantId, int $establecimientoId): void
    {
        $esAdmin = User::query()->whereKey($usuarioId)
            ->whereHas('roles', fn ($query) => $query->where('roles.slug', 'admin')->where('usuario_rol.tenant_id', $tenantId))
            ->exists();

        if (! $esAdmin && ! DB::table('usuario_establecimiento')
            ->where('tenant_id', $tenantId)
            ->where('usuario_id', $usuarioId)
            ->where('establecimiento_id', $establecimientoId)
            ->exists()) {
            throw ValidationException::withMessages(['establecimiento' => 'El usuario no tiene acceso al establecimiento.']);
        }
    }

    private function almacenActivo(int $tenantId, int $almacenId): Almacen
    {
        return Almacen::query()->where('tenant_id', $tenantId)->where('activo', true)->find($almacenId)
            ?? throw ValidationException::withMessages(['almacen' => 'El almacén no pertenece al tenant o está inactivo.']);
    }

    private function validarUbicacion(int $tenantId, int $almacenId, int $ubicacionId): void
    {
        if (! Ubicacion::query()->where('tenant_id', $tenantId)->where('almacen_id', $almacenId)->whereKey($ubicacionId)->exists()) {
            throw ValidationException::withMessages(['ubicacion' => 'La ubicación no corresponde al almacén indicado.']);
        }
    }

    private function ubicacionPrincipal(int $tenantId, int $almacenId): int
    {
        return (int) (Ubicacion::query()->where('tenant_id', $tenantId)->where('almacen_id', $almacenId)->orderBy('id')->value('id')
            ?? throw ValidationException::withMessages(['ubicacion' => 'El almacén destino necesita una ubicación.']));
    }

    private function folio(int $tenantId): string
    {
        do {
            $folio = 'TR-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        } while (Transferencia::query()->where('tenant_id', $tenantId)->where('folio', $folio)->exists());

        return $folio;
    }
}

<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\Establecimiento;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstablecimientoService
{
    public function crear(int $tenantId, array $datos): Establecimiento
    {
        if (! in_array($datos['tipo'] ?? null, ['sucursal', 'centro_distribucion'], true)) {
            throw ValidationException::withMessages(['tipo' => 'El establecimiento debe ser sucursal o centro de distribución.']);
        }

        return Establecimiento::query()->create([
            'tenant_id' => $tenantId,
            'codigo' => $datos['codigo'] ?? throw ValidationException::withMessages(['codigo' => 'El código es obligatorio.']),
            'nombre' => $datos['nombre'] ?? throw ValidationException::withMessages(['nombre' => 'El nombre es obligatorio.']),
            'tipo' => $datos['tipo'],
            'email' => $datos['email'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'activo' => true,
        ]);
    }

    public function crearAlmacen(int $tenantId, int $establecimientoId, string $codigo, string $nombre): Almacen
    {
        return DB::transaction(function () use ($tenantId, $establecimientoId, $codigo, $nombre): Almacen {
            $establecimiento = Establecimiento::query()
                ->where('tenant_id', $tenantId)
                ->where('activo', true)
                ->find($establecimientoId);

            if ($establecimiento === null) {
                throw ValidationException::withMessages(['establecimiento_id' => 'El establecimiento no pertenece al tenant o está inactivo.']);
            }

            $almacen = Almacen::query()->create([
                'tenant_id' => $tenantId,
                'establecimiento_id' => $establecimientoId,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'activo' => true,
            ]);

            Ubicacion::query()->create([
                'tenant_id' => $tenantId,
                'almacen_id' => $almacen->getKey(),
                'nombre' => 'Principal',
                'codigo' => $codigo.'-PRINCIPAL',
            ]);

            return $almacen;
        });
    }

    public function asignarUsuario(int $tenantId, int $usuarioId, int $establecimientoId, ?int $asignadoPor = null): void
    {
        $usuario = User::query()->where('tenant_id', $tenantId)->find($usuarioId);
        $establecimiento = Establecimiento::query()->where('tenant_id', $tenantId)->find($establecimientoId);

        if ($usuario === null || $establecimiento === null) {
            throw ValidationException::withMessages(['establecimiento' => 'El usuario y el establecimiento deben pertenecer al mismo tenant.']);
        }

        DB::table('usuario_establecimiento')->updateOrInsert(
            ['usuario_id' => $usuarioId, 'establecimiento_id' => $establecimientoId],
            ['tenant_id' => $tenantId, 'asignado_por' => $asignadoPor, 'updated_at' => now(), 'created_at' => now()],
        );
    }
}

<?php
namespace App\Services;

use App\Models\{ConfiguracionInventario,SolicitudInventario,Tenant,TipoInventario};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantService
{
    public function crear(string $nombre, int $tipoInventarioId, ?string $email = null): Tenant
    {
        return DB::transaction(function () use ($nombre, $tipoInventarioId, $email) {
            $tipo = TipoInventario::query()->findOrFail($tipoInventarioId);
            $base = Str::slug($nombre) ?: 'tenant';
            // El sufijo UUID evita colisiones sin depender de un contador de solicitudes.
            $tenant = Tenant::query()->create([
                'nombre' => $nombre,
                'slug' => $base.'-'.Str::lower(Str::random(8)),
                'email' => $email,
                'tipo_inventario_id' => $tipo->getKey(),
                'estado' => 'pendiente',
            ]);
            ConfiguracionInventario::query()->create([
                'tenant_id' => $tenant->getKey(),
                'controlar_lotes' => false,
                'controlar_caducidad' => false,
                'controlar_series' => false,
                'permitir_stock_negativo' => false,
            ]);
            return $tenant;
        });
    }

    public function aprobarSolicitud(int $solicitudId): Tenant
    {
        return DB::transaction(function () use ($solicitudId) {
            $solicitud = SolicitudInventario::query()->lockForUpdate()->findOrFail($solicitudId);
            if ($solicitud->estado !== 'pendiente' || $solicitud->tenant_id !== null) {
                throw ValidationException::withMessages(['solicitud' => 'La solicitud ya fue atendida.']);
            }
            $tenant = $this->crear($solicitud->nombre_negocio, (int) $solicitud->tipo_inventario_id, $solicitud->email_contacto);
            $solicitud->update(['estado' => 'aprobada', 'tenant_id' => $tenant->getKey()]);
            return $tenant;
        });
    }

    public function cambiarEstado(int $tenantId, string $estado): Tenant
    {
        if (!in_array($estado, ['pendiente', 'activo', 'suspendido', 'cancelado'], true)) {
            throw ValidationException::withMessages(['estado' => 'Estado de tenant inválido.']);
        }
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->update(['estado' => $estado]);
        return $tenant->refresh();
    }
}

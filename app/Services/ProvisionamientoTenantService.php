<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Modulo;
use App\Models\ModuloTenant;
use App\Models\PlanSuscripcion;
use App\Models\Rol;
use App\Models\SolicitudInventario;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProvisionamientoTenantService
{
    public function aprobarSolicitud(
        int $solicitudId,
        int $planId,
        string $adminNombre,
        array $modulos,
        int $actorId,
    ): array {
        return DB::transaction(function () use ($solicitudId, $planId, $adminNombre, $modulos, $actorId): array {
            $solicitud = SolicitudInventario::query()->lockForUpdate()->findOrFail($solicitudId);

            if ($solicitud->estado !== 'pendiente' || $solicitud->tenant_id !== null) {
                throw ValidationException::withMessages(['solicitud' => 'Esta solicitud ya fue atendida.']);
            }

            $plan = PlanSuscripcion::query()->where('activo', true)->findOrFail($planId);
            $tipoFerreteria = $solicitud->tipoInventario;

            if ($tipoFerreteria === null || Str::lower($tipoFerreteria->nombre) !== 'ferretería') {
                throw ValidationException::withMessages(['solicitud' => 'La solicitud no corresponde al tipo de negocio ferretero configurado.']);
            }

            $email = Str::lower($solicitud->email_contacto);
            if (User::query()->where('email', $email)->exists()) {
                throw ValidationException::withMessages(['adminEmail' => 'Ya existe un usuario con el correo de contacto de esta solicitud.']);
            }

            $modulosDisponibles = Modulo::query()
                ->where('activo', true)
                ->whereIn('clave', $modulos)
                ->pluck('id', 'clave');

            if ($modulosDisponibles->count() !== count(array_unique($modulos))) {
                throw ValidationException::withMessages(['modulos' => 'Uno o más módulos ya no están disponibles en la plataforma.']);
            }

            $tenant = app(TenantService::class)->crear(
                $solicitud->nombre_negocio,
                (int) $solicitud->tipo_inventario_id,
                $solicitud->email_contacto,
            );
            // La aprobación administrativa crea el acceso inicial, pero no activa
            // el servicio: el cliente debe personalizarlo y completar el primer cobro.
            $tenant->update(['estado' => 'configuracion_pendiente']);

            $suscripcion = SuscripcionTenant::query()->create([
                'tenant_id' => $tenant->getKey(),
                'plan_id' => $plan->getKey(),
                'estado' => 'personalizacion_pendiente',
                'proveedor' => 'mercadopago',
                'inicio_at' => now(),
                'fin_at' => null,
            ]);

            foreach ($modulosDisponibles as $moduloId) {
                ModuloTenant::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'modulo_id' => $moduloId,
                    'habilitado' => true,
                ]);
            }

            $passwordTemporal = Str::random(32);
            $administrador = new User;
            $administrador->forceFill([
                'name' => $adminNombre,
                'email' => $email,
                'password' => $passwordTemporal,
                'tenant_id' => $tenant->getKey(),
                'activo' => true,
                'email_verified_at' => now(),
            ]);
            $administrador->save();

            app(TenantRoleService::class)->inicializar($tenant);
            $rolAdmin = Rol::query()->where('tenant_id', $tenant->getKey())->where('slug', 'admin')->firstOrFail();
            app(AccesoTenantService::class)->asignarRol((int) $administrador->getKey(), (int) $rolAdmin->getKey(), (int) $tenant->getKey());

            $solicitud->update([
                'estado' => 'aprobada',
                'tenant_id' => $tenant->getKey(),
                'observaciones' => trim(($solicitud->observaciones ? $solicitud->observaciones."\n" : '').'Aprobada por superadministración. Suscripción '.$suscripcion->getKey().'.'),
            ]);

            $this->auditar($actorId, 'solicitud_aprobada', 'SolicitudInventario', $solicitud->getKey(), [
                'tenant_id' => $tenant->getKey(),
                'plan_id' => $plan->getKey(),
                'admin_id' => $administrador->getKey(),
                'modulos' => $modulosDisponibles->keys()->values()->all(),
            ]);

            return compact('tenant', 'administrador', 'plan', 'passwordTemporal');
        });
    }

    public function rechazarSolicitud(int $solicitudId, string $motivo, int $actorId): SolicitudInventario
    {
        return DB::transaction(function () use ($solicitudId, $motivo, $actorId): SolicitudInventario {
            $solicitud = SolicitudInventario::query()->lockForUpdate()->findOrFail($solicitudId);
            if ($solicitud->estado !== 'pendiente' || $solicitud->tenant_id !== null) {
                throw ValidationException::withMessages(['solicitud' => 'Esta solicitud ya fue atendida.']);
            }

            $solicitud->update([
                'estado' => 'rechazada',
                'observaciones' => trim(($solicitud->observaciones ? $solicitud->observaciones."\n" : '').'Rechazada: '.$motivo),
            ]);

            $this->auditar($actorId, 'solicitud_rechazada', 'SolicitudInventario', $solicitud->getKey(), ['motivo' => $motivo]);

            return $solicitud->refresh();
        });
    }

    private function auditar(int $actorId, string $accion, string $entidad, int $entidadId, array $datos): void
    {
        Auditoria::query()->create([
            'usuario_id' => $actorId,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'datos_nuevos' => $datos,
            'ip' => request()->ip(),
            'agente_usuario' => request()->userAgent(),
        ]);
    }
}

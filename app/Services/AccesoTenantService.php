<?php
namespace App\Services;

use App\Models\{Rol,Tenant,User,UsuarioRol};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccesoTenantService
{
    public function asignarRol(int $usuarioId, int $rolId, ?int $tenantId): UsuarioRol
    {
        return DB::transaction(function () use ($usuarioId, $rolId, $tenantId) {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuarioId);
            $rol = Rol::query()->findOrFail($rolId);
            if ($tenantId === null) {
                if ($rol->tenant_id !== null || !$rol->es_sistema) {
                    throw ValidationException::withMessages(['rol' => 'Solo un rol global del sistema puede asignarse sin tenant.']);
                }
            } else {
                Tenant::query()->findOrFail($tenantId);
                if ((int) $usuario->tenant_id !== $tenantId || (int) $rol->tenant_id !== $tenantId || $rol->es_sistema) {
                    throw ValidationException::withMessages(['rol' => 'Usuario y rol deben pertenecer al mismo tenant.']);
                }
            }
            return UsuarioRol::query()->firstOrCreate([
                'usuario_id' => $usuarioId, 'rol_id' => $rolId,
            ], ['tenant_id' => $tenantId]);
        });
    }

    public function tienePermiso(User $usuario, string $clave, ?int $tenantId): bool
    {
        if (!$usuario->activo) return false;
        return DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id')
            ->join('permisos as p', 'p.id', '=', 'rp.permiso_id')
            ->where('ur.usuario_id', $usuario->getKey())
            ->where('p.clave', $clave)
            ->where(function ($q) use ($tenantId) {
                if ($tenantId !== null) {
                    $q->where(fn ($tenant) => $tenant->where('ur.tenant_id', $tenantId)->where('r.tenant_id', $tenantId));
                } else {
                    $q->whereNull('ur.tenant_id')->whereNull('r.tenant_id')->where('r.es_sistema', true);
                }
            })->exists();
    }
}

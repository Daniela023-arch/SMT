<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantRoleService
{
    /** @return array<string, Rol> */
    public function inicializar(Tenant $tenant): array
    {
        $definitions = [
            'admin' => ['Administrador', ['tenant.%']],
            'gerente' => ['Gerente', ['tenant.establecimientos.ver', 'tenant.establecimientos.gestionar', 'tenant.productos.ver', 'tenant.productos.gestionar', 'tenant.proveedores.gestionar', 'tenant.reportes.ver', 'tenant.usuarios.gestionar', 'tenant.inventario.%', 'tenant.ventas.%', 'tenant.transferencias.%']],
            'ventas' => ['Ventas', ['tenant.ventas.%']],
            'almacen' => ['Almacén', ['tenant.inventario.%', 'tenant.transferencias.%', 'tenant.productos.ver', 'tenant.establecimientos.ver']],
        ];
        $roles = [];

        foreach ($definitions as $slug => [$name, $patterns]) {
            $role = Rol::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'slug' => $slug],
                ['nombre' => $name, 'es_sistema' => false],
            );
            $permissionIds = Permiso::query()->where(function ($query) use ($patterns): void {
                foreach ($patterns as $pattern) {
                    str_contains($pattern, '%')
                        ? $query->orWhere('clave', 'like', $pattern)
                        : $query->orWhere('clave', $pattern);
                }
            })->pluck('id');

            DB::table('rol_permiso')->where('rol_id', $role->getKey())->whereNotIn('permiso_id', $permissionIds)->delete();

            foreach ($permissionIds as $permissionId) {
                DB::table('rol_permiso')->insertOrIgnore(['rol_id' => $role->getKey(), 'permiso_id' => $permissionId]);
            }
            $roles[$slug] = $role;
        }

        return $roles;
    }
}

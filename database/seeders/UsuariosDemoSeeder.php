<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Establecimiento;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AccesoTenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Cuentas y asignaciones de demostración para los tenants de ferreterías.
 * Nunca modifica contraseñas de usuarios existentes ni crea superadministradores.
 */
class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Los usuarios de demostración solo se pueden crear con APP_ENV=local.');
        }

        $password = env('DEMO_TENANT_PASSWORD') ?: 'demo1234';
        $cuentas = [
            'ferreteria-alfa' => [
                'admin' => ['Administración Ferretería Alfa', 'admin@ferreteriaalfa.mx'],
                'gerente' => ['Gerencia Ferretería Alfa', 'gerente@ferreteriaalfa.mx'],
                'ventas' => ['Encargado de ventas Alfa', 'ventas@ferreteriaalfa.mx'],
                'almacen' => ['Encargado de almacén Alfa', 'almacen@ferreteriaalfa.mx'],
            ],
            'ferreteria-beta' => [
                'admin' => ['Administración Ferretería Beta', 'admin@ferreteriabeta.mx'],
                'gerente' => ['Gerencia Ferretería Beta', 'gerente@ferreteriabeta.mx'],
                'ventas' => ['Encargado de ventas Beta', 'ventas@ferreteriabeta.mx'],
                'almacen' => ['Encargado de almacén Beta', 'almacen@ferreteriabeta.mx'],
            ],
            'grupo-ferretero-central' => [
                'admin' => ['Administración Grupo Ferretero Central', 'admin@grupoferreterocentral.mx'],
            ],
        ];

        $permisosPorRol = [
            'admin' => ['tenant.%'],
            'gerente' => ['tenant.establecimientos.ver', 'tenant.establecimientos.gestionar', 'tenant.productos.ver', 'tenant.productos.gestionar', 'tenant.proveedores.gestionar', 'tenant.reportes.ver', 'tenant.usuarios.gestionar', 'tenant.inventario.%', 'tenant.ventas.%', 'tenant.transferencias.%'],
            'ventas' => ['tenant.ventas.%'],
            'almacen' => ['tenant.inventario.%', 'tenant.transferencias.%', 'tenant.productos.ver', 'tenant.establecimientos.ver'],
        ];
        $nombresRoles = ['admin' => 'Administrador', 'gerente' => 'Gerente', 'ventas' => 'Ventas', 'almacen' => 'Almacén'];
        $creadas = [];

        foreach ($cuentas as $slugTenant => $definiciones) {
            $tenant = Tenant::query()->where('slug', $slugTenant)->firstOrFail();

            DB::transaction(function () use ($tenant, $definiciones, $password, $permisosPorRol, $nombresRoles, &$creadas): void {
                $roles = [];
                foreach ($nombresRoles as $slugRol => $nombreRol) {
                    $rol = Rol::query()->firstOrCreate(
                        ['tenant_id' => $tenant->getKey(), 'slug' => $slugRol],
                        ['nombre' => $nombreRol, 'es_sistema' => false],
                    );
                    if ($rol->es_sistema) {
                        throw new RuntimeException('El rol '.$slugRol.' no debe ser un rol de sistema dentro de un tenant.');
                    }

                    $idsPermisos = Permiso::query()->where(function ($consulta) use ($permisosPorRol, $slugRol): void {
                        foreach ($permisosPorRol[$slugRol] as $patron) {
                            if (str_contains($patron, '%')) {
                                $consulta->orWhere('clave', 'like', $patron);
                            } else {
                                $consulta->orWhere('clave', $patron);
                            }
                        }
                    })->pluck('id');
                    DB::table('rol_permiso')->where('rol_id', $rol->getKey())->whereNotIn('permiso_id', $idsPermisos)->delete();
                    foreach ($idsPermisos as $permisoId) {
                        DB::table('rol_permiso')->insertOrIgnore([
                            'rol_id' => $rol->getKey(), 'permiso_id' => $permisoId,
                        ]);
                    }
                    $roles[$slugRol] = $rol;
                }

                $usuarios = [];
                foreach ($definiciones as $slugRol => [$nombre, $email]) {
                    $usuario = User::query()->firstOrNew(['email' => $email]);
                    if ($usuario->exists && (int) $usuario->tenant_id !== (int) $tenant->getKey()) {
                        throw new RuntimeException('La cuenta '.$email.' ya pertenece a otro tenant.');
                    }
                    if (! $usuario->exists) {
                        $usuario->forceFill([
                            'name' => $nombre,
                            'email' => $email,
                            'password' => $password,
                            'tenant_id' => $tenant->getKey(),
                            'activo' => true,
                            'email_verified_at' => now(),
                        ])->save();
                        $creadas[] = $email;
                    } else {
                        $usuario->forceFill(['activo' => true, 'email_verified_at' => $usuario->email_verified_at ?? now()])->save();
                    }

                    DB::table('usuario_rol')->where('usuario_id', $usuario->getKey())->where('tenant_id', $tenant->getKey())->delete();
                    app(AccesoTenantService::class)->asignarRol(
                        (int) $usuario->getKey(), (int) $roles[$slugRol]->getKey(), (int) $tenant->getKey(),
                    );
                    $usuarios[$slugRol] = $usuario;
                }

                // Grupo Central representa el caso donde el administrador aún trabaja sin personal.
                if ($tenant->slug === 'grupo-ferretero-central') {
                    foreach ([
                        'gerente@grupoferreterocentral.mx',
                        'ventas@grupoferreterocentral.mx',
                        'almacen@grupoferreterocentral.mx',
                    ] as $correoPersonalDemo) {
                        $personalAnterior = User::query()->where('email', $correoPersonalDemo)->where('tenant_id', $tenant->getKey())->first();
                        if ($personalAnterior === null) continue;

                        DB::table('usuario_rol')->where('usuario_id', $personalAnterior->getKey())->where('tenant_id', $tenant->getKey())->delete();
                        DB::table('usuario_establecimiento')->where('usuario_id', $personalAnterior->getKey())->where('tenant_id', $tenant->getKey())->delete();
                        DB::table('usuario_almacen')->where('usuario_id', $personalAnterior->getKey())->where('tenant_id', $tenant->getKey())->delete();
                        $personalAnterior->forceFill(['activo' => false])->save();
                    }
                }

                $administradorId = (int) $usuarios['admin']->getKey();
                $sucursales = Establecimiento::query()->where('tenant_id', $tenant->getKey())->where('activo', true)->orderBy('id')->get();
                $almacenes = Almacen::query()->where('tenant_id', $tenant->getKey())->where('activo', true)
                    ->whereIn('establecimiento_id', $sucursales->modelKeys())->orderBy('id')->get();
                if ($sucursales->isEmpty() || $almacenes->isEmpty()) {
                    throw new RuntimeException('Cada tenant demo debe tener al menos una sucursal activa y un almacén activo.');
                }

                $almacenVentas = $almacenes->first();
                $sucursalesPorRol = [
                    'admin' => $sucursales,
                    'gerente' => $sucursales,
                    'ventas' => $sucursales->where('id', $almacenVentas->establecimiento_id),
                    'almacen' => $sucursales->whereIn('id', $almacenes->pluck('establecimiento_id')->unique()),
                ];
                $almacenesPorRol = [
                    'admin' => collect(),
                    'gerente' => collect(),
                    'ventas' => collect([$almacenVentas]),
                    'almacen' => $almacenes,
                ];

                foreach ($usuarios as $slugRol => $usuario) {
                    DB::table('usuario_establecimiento')->where('tenant_id', $tenant->getKey())->where('usuario_id', $usuario->getKey())->delete();
                    DB::table('usuario_almacen')->where('tenant_id', $tenant->getKey())->where('usuario_id', $usuario->getKey())->delete();
                    foreach ($sucursalesPorRol[$slugRol] as $sucursal) {
                        DB::table('usuario_establecimiento')->insert([
                            'usuario_id' => $usuario->getKey(), 'establecimiento_id' => $sucursal->getKey(),
                            'tenant_id' => $tenant->getKey(), 'asignado_por' => $administradorId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    foreach ($almacenesPorRol[$slugRol] as $almacen) {
                        DB::table('usuario_almacen')->insert([
                            'usuario_id' => $usuario->getKey(), 'almacen_id' => $almacen->getKey(),
                            'tenant_id' => $tenant->getKey(), 'asignado_por' => $administradorId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            });
        }

        if ($creadas !== []) {
            $this->command?->info('Usuarios demo creados: '.implode(', ', $creadas));
            $this->command?->warn('Contraseña inicial solo para los usuarios nuevos: '.$password);
        } else {
            $this->command?->info('Los usuarios de demostración ya existían. No se alteraron sus contraseñas.');
        }
    }
}

<?php

namespace App\Services;

use App\Models\ConfiguracionTenant;
use App\Models\Establecimiento;
use App\Models\Rol;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UsuarioTenantService
{
    public function puedeAdministrarPersonal(User $actor): bool
    {
        return $this->esAdmin($actor) || $this->esGerente($actor);
    }

    public function sucursalesDisponibles(User $actor)
    {
        $query = Establecimiento::query()->where('tenant_id', $actor->tenant_id)->where('activo', true)->orderBy('nombre');
        if ($this->esGerente($actor)) {
            $query->whereHas('usuarios', fn ($users) => $users->where('users.id', $actor->getKey())->where('usuario_establecimiento.tenant_id', $actor->tenant_id));
        }

        return $query->get(['id', 'nombre', 'codigo']);
    }

    public function usuarios(User $actor)
    {
        abort_unless($this->puedeAdministrarPersonal($actor), 403);
        $query = User::query()->where('tenant_id', $actor->tenant_id)
            ->with(['roles' => fn ($roles) => $roles->wherePivot('tenant_id', $actor->tenant_id), 'establecimientos:id,nombre', 'almacenesAsignados:id,nombre,establecimiento_id']);

        if ($this->esGerente($actor)) {
            $branchIds = $this->sucursalesDisponibles($actor)->modelKeys();
            $query->with([
                'establecimientos' => fn ($branches) => $branches->whereIn('establecimientos.id', $branchIds),
                'almacenesAsignados' => fn ($warehouses) => $warehouses->whereIn('establecimiento_id', $branchIds),
            ])
                ->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('roles.slug', ['admin', 'gerente'])->where('usuario_rol.tenant_id', $actor->tenant_id))
                ->whereHas('establecimientos', fn ($branches) => $branches->whereIn('establecimientos.id', $branchIds))
                ->whereDoesntHave('establecimientos', fn ($branches) => $branches->whereNotIn('establecimientos.id', $branchIds));
        }

        return $query->orderBy('name')->get();
    }

    public function guardar(User $actor, array $datos, ?int $usuarioId = null): array
    {
        abort_unless($this->puedeAdministrarPersonal($actor), 403);
        $tenantId = (int) $actor->tenant_id;
        $roleSlug = (string) $datos['rol'];
        $puedeVerPuntoVenta = $roleSlug === 'gerente' ? (bool) ($datos['puede_ver_punto_venta'] ?? true) : true;
        $puedeVerAlmacen = $roleSlug === 'gerente' ? (bool) ($datos['puede_ver_almacen'] ?? true) : true;
        $branches = array_values(array_unique(array_map('intval', $datos['establecimientos'] ?? [])));
        $warehouseIds = array_values(array_unique(array_map('intval', $datos['almacenes'] ?? [])));

        if (! in_array($roleSlug, ['gerente', 'ventas', 'almacen'], true)) {
            throw ValidationException::withMessages(['rol' => 'Elige un rol operativo disponible.']);
        }

        $tenant = Tenant::query()->findOrFail($tenantId);
        app(TenantRoleService::class)->inicializar($tenant);

        if (! $this->personalHabilitado($tenantId)) {
            throw ValidationException::withMessages(['personal' => 'Primero habilita la gestión de personal en la configuración del equipo.']);
        }

        if ($usuarioId === null) {
            $subscription = SuscripcionTenant::query()->with('plan')->where('tenant_id', $tenantId)
                ->whereIn('estado', ['activa', 'activo', 'prueba'])->latest('inicio_at')->first();
            $limit = $subscription?->plan?->limite_usuarios;
            if ($limit !== null && Tenant::query()->findOrFail($tenantId)->usuarios()->count() >= $limit) {
                throw ValidationException::withMessages(['email' => 'Alcanzaste el límite de usuarios de tu plan.']);
            }
        }

        $admin = $this->esAdmin($actor);
        if (! $admin && $roleSlug === 'gerente') {
            throw ValidationException::withMessages(['rol' => 'Solo el administrador del tenant puede crear o asignar gerentes.']);
        }

        $managerBranches = $this->sucursalesDisponibles($actor)->modelKeys();
        if (! $admin && array_diff($branches, $managerBranches) !== []) {
            throw ValidationException::withMessages(['establecimientos' => 'El gerente solo puede asignar personal a sus sucursales.']);
        }

        if ($roleSlug === 'ventas' && count($warehouseIds) === 1) {
            $point = DB::table('almacenes')->where('tenant_id', $tenantId)->where('activo', true)->where('id', $warehouseIds[0])->first(['establecimiento_id']);
            if ($point !== null) $branches = [(int) $point->establecimiento_id];
        }

        $validBranches = Establecimiento::query()->where('tenant_id', $tenantId)->where('activo', true)->whereIn('id', $branches)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($validBranches) !== count($branches)) {
            throw ValidationException::withMessages(['establecimientos' => 'Una o más sucursales no pertenecen a esta ferretería o están inactivas.']);
        }

        if ($branches === []) {
            throw ValidationException::withMessages(['establecimientos' => 'Asigna al usuario a una o más sucursales.']);
        }
        if ($roleSlug === 'almacen') {
            $validWarehouses = DB::table('almacenes')->where('tenant_id', $tenantId)->where('activo', true)->whereIn('id', $warehouseIds)->get(['id', 'establecimiento_id']);
            if ($warehouseIds === [] || $validWarehouses->count() !== count($warehouseIds)) {
                throw ValidationException::withMessages(['almacenes' => 'Selecciona al menos un almacén activo de esta ferretería.']);
            }
            $warehouseBranches = $validWarehouses->pluck('establecimiento_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
            if (array_diff($warehouseBranches, $branches) !== []) {
                throw ValidationException::withMessages(['almacenes' => 'Cada almacén debe pertenecer a una de las sucursales seleccionadas.']);
            }
            if (! $admin && array_diff($warehouseBranches, $managerBranches) !== []) {
                throw ValidationException::withMessages(['almacenes' => 'Solo puedes asignar almacenes que pertenecen a tus sucursales.']);
            }
            $branches = $warehouseBranches;
        } elseif ($roleSlug === 'ventas') {
            $validWarehouses = DB::table('almacenes')->where('tenant_id', $tenantId)->where('activo', true)
                ->whereIn('id', $warehouseIds)->get(['id', 'establecimiento_id']);
            if (count($warehouseIds) !== 1 || $validWarehouses->count() !== 1) {
                throw ValidationException::withMessages(['almacenes' => 'Asigna exactamente un punto de venta activo a cada encargado de ventas.']);
            }
            $warehouseBranch = (int) $validWarehouses->first()->establecimiento_id;
            if (! in_array($warehouseBranch, $branches, true)) {
                throw ValidationException::withMessages(['almacenes' => 'El punto de venta debe pertenecer a una sucursal asignada.']);
            }
            if (! $admin && ! in_array($warehouseBranch, $managerBranches, true)) {
                throw ValidationException::withMessages(['almacenes' => 'Solo puedes asignar puntos de venta de tus sucursales.']);
            }
            $branches = [$warehouseBranch];
        } else {
            $warehouseIds = [];
        }

        $role = Rol::query()->where('tenant_id', $tenantId)->where('slug', $roleSlug)->where('es_sistema', false)->first();
        if ($role === null) {
            throw ValidationException::withMessages(['rol' => 'El rol seleccionado aún no está configurado para este tenant. Ejecuta los seeders de roles y vuelve a intentarlo.']);
        }

        return DB::transaction(function () use ($actor, $datos, $usuarioId, $tenantId, $role, $roleSlug, $branches, $warehouseIds, $puedeVerPuntoVenta, $puedeVerAlmacen): array {
            $target = $usuarioId === null
                ? new User
                : User::query()->where('tenant_id', $tenantId)->lockForUpdate()->findOrFail($usuarioId);

            if ($target->exists) {
                $this->autorizarObjetivo($actor, $target);
                if ((int) $target->getKey() === (int) $actor->getKey()) {
                    throw ValidationException::withMessages(['usuario' => 'Administra tu propia cuenta desde Mi perfil.']);
                }
            }

            $antes = $target->exists ? $this->resumen($target->load(['roles', 'establecimientos', 'almacenesAsignados'])) : null;
            $target->forceFill([
                'name' => $datos['nombre'],
                'email' => Str::lower($datos['email']),
                'activo' => (bool) ($datos['activo'] ?? true),
                'tenant_id' => $tenantId,
                'puede_ver_punto_venta' => $puedeVerPuntoVenta,
                'puede_ver_almacen' => $puedeVerAlmacen,
            ]);
            if (! $target->exists) {
                $target->forceFill(['password' => Str::random(48), 'email_verified_at' => now()]);
            }
            $target->save();

            DB::table('usuario_rol')->where('usuario_id', $target->getKey())->where('tenant_id', $tenantId)->delete();
            DB::table('usuario_rol')->insert([
                'usuario_id' => $target->getKey(), 'rol_id' => $role->getKey(), 'tenant_id' => $tenantId,
            ]);

            DB::table('usuario_establecimiento')->where('usuario_id', $target->getKey())->where('tenant_id', $tenantId)->delete();
            $now = now();
            if ($branches !== []) {
                DB::table('usuario_establecimiento')->insert(array_map(fn (int $branchId): array => [
                    'usuario_id' => $target->getKey(), 'establecimiento_id' => $branchId, 'tenant_id' => $tenantId,
                    'asignado_por' => $actor->getKey(), 'created_at' => $now, 'updated_at' => $now,
                ], $branches));
            }

            DB::table('usuario_almacen')->where('usuario_id', $target->getKey())->where('tenant_id', $tenantId)->delete();
            if ($warehouseIds !== []) {
                DB::table('usuario_almacen')->insert(array_map(fn (int $warehouseId): array => [
                    'usuario_id' => $target->getKey(), 'almacen_id' => $warehouseId, 'tenant_id' => $tenantId,
                    'asignado_por' => $actor->getKey(), 'created_at' => $now, 'updated_at' => $now,
                ], $warehouseIds));
            }

            $despues = $this->resumen($target->fresh(['roles', 'establecimientos', 'almacenesAsignados']));
            app(AuditoriaService::class)->registrar(
                (int) $actor->getKey(),
                $usuarioId === null ? 'usuario_tenant_creado' : 'usuario_tenant_actualizado',
                'User',
                (int) $target->getKey(),
                $tenantId,
                $antes,
                $despues,
            );

            $resetUrl = null;
            if ($usuarioId === null) {
                $token = Password::broker()->createToken($target);
                $resetUrl = URL::route('password.reset', ['token' => $token, 'email' => $target->email]);
            }

            return ['usuario' => $target->fresh(['roles', 'establecimientos', 'almacenesAsignados']), 'enlaceInicial' => $resetUrl];
        });
    }

    public function cambiarActivo(User $actor, int $usuarioId): bool
    {
        abort_unless($this->puedeAdministrarPersonal($actor), 403);
        $target = User::query()->where('tenant_id', $actor->tenant_id)->with('roles')->findOrFail($usuarioId);
        $this->autorizarObjetivo($actor, $target);
        if ((int) $target->getKey() === (int) $actor->getKey()) {
            throw ValidationException::withMessages(['usuario' => 'No puedes desactivar tu propia cuenta.']);
        }

        $antes = (bool) $target->activo;
        $target->forceFill(['activo' => ! $antes])->save();
        app(AuditoriaService::class)->registrar(
            (int) $actor->getKey(), $antes ? 'usuario_tenant_desactivado' : 'usuario_tenant_activado',
            'User', (int) $target->getKey(), (int) $actor->tenant_id,
            ['activo' => $antes], ['activo' => ! $antes],
        );

        return ! $antes;
    }

    public function enlaceRestablecimiento(User $actor, int $usuarioId): string
    {
        abort_unless($this->puedeAdministrarPersonal($actor), 403);
        $target = User::query()->where('tenant_id', $actor->tenant_id)->with('roles')->findOrFail($usuarioId);
        $this->autorizarObjetivo($actor, $target);
        $token = Password::broker()->createToken($target);
        app(AuditoriaService::class)->registrar(
            (int) $actor->getKey(), 'usuario_tenant_enlace_restablecimiento', 'User', (int) $target->getKey(),
            (int) $actor->tenant_id, null, ['email' => $target->email],
        );

        return URL::route('password.reset', ['token' => $token, 'email' => $target->email]);
    }

    public function cambiarPersonalHabilitado(User $actor, bool $habilitado): void
    {
        abort_unless($this->esAdmin($actor), 403);
        $tenant = Tenant::query()->findOrFail($actor->tenant_id);
        $config = ConfiguracionTenant::query()->firstOrCreate(['tenant_id' => $tenant->getKey()]);
        $preferences = $config->preferencias ?? [];
        $antes = (bool) ($preferences['personal_habilitado'] ?? true);
        if ($antes === $habilitado) {
            return;
        }

        if (! $habilitado && $tenant->usuarios()->where('activo', true)
            ->where('users.id', '!=', $actor->getKey())->whereHas('roles', fn ($roles) => $roles->where('roles.slug', '!=', 'admin')->where('usuario_rol.tenant_id', $tenant->getKey()))->exists()) {
            throw ValidationException::withMessages(['personal' => 'Desactiva primero a los encargados y gerentes activos.']);
        }

        $preferences['personal_habilitado'] = $habilitado;
        $config->forceFill(['preferencias' => $preferences])->save();
        app(AuditoriaService::class)->registrar(
            (int) $actor->getKey(), 'tenant_personal_habilitado', 'ConfiguracionTenant', (int) $config->getKey(),
            (int) $tenant->getKey(), ['personal_habilitado' => $antes], ['personal_habilitado' => $habilitado],
        );
    }

    public function personalHabilitado(int $tenantId): bool
    {
        $preferences = ConfiguracionTenant::query()->where('tenant_id', $tenantId)->first()?->preferencias ?? [];

        return (bool) ($preferences['personal_habilitado'] ?? true);
    }

    private function esAdmin(User $user): bool
    {
        return $user->roles()->where('roles.slug', 'admin')->where('roles.tenant_id', $user->tenant_id)
            ->wherePivot('tenant_id', $user->tenant_id)->exists();
    }

    private function esGerente(User $user): bool
    {
        return $user->roles()->where('roles.slug', 'gerente')->where('roles.tenant_id', $user->tenant_id)
            ->wherePivot('tenant_id', $user->tenant_id)->exists();
    }

    private function autorizarObjetivo(User $actor, User $target): void
    {
        $targetRole = $target->roles->first(fn ($role) => (int) $role->tenant_id === (int) $actor->tenant_id);
        if ($targetRole === null || $targetRole->slug === 'admin') {
            throw ValidationException::withMessages(['usuario' => 'No tienes permiso para modificar a este administrador.']);
        }
        if (! $this->esAdmin($actor)) {
            if ($targetRole->slug === 'gerente' || ! in_array($targetRole->slug, ['ventas', 'almacen'], true)) {
                throw ValidationException::withMessages(['usuario' => 'El gerente solo puede administrar encargados de sus sucursales.']);
            }
            $branchIds = $this->sucursalesDisponibles($actor)->modelKeys();
            if (! $target->establecimientos()->whereIn('establecimientos.id', $branchIds)->exists()
                || $target->establecimientos()->whereNotIn('establecimientos.id', $branchIds)->exists()) {
                throw ValidationException::withMessages(['usuario' => 'Este usuario no pertenece a tus sucursales asignadas.']);
            }
        }
    }

    private function resumen(User $user): array
    {
        return [
            'nombre' => $user->name,
            'email' => $user->email,
            'activo' => (bool) $user->activo,
            'rol' => $user->roles->first()?->slug,
            'puede_ver_punto_venta' => (bool) $user->puede_ver_punto_venta,
            'puede_ver_almacen' => (bool) $user->puede_ver_almacen,
            'establecimientos' => $user->establecimientos->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'almacenes' => $user->almacenesAsignados->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }
}

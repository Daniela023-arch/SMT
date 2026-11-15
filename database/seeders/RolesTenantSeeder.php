<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Services\TenantRoleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTenantSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tenant::query()->cursor() as $tenant) {
            app(TenantRoleService::class)->inicializar($tenant);
        }

        $assignments = DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->join('usuario_establecimiento as ue', function ($join): void {
                $join->on('ue.usuario_id', '=', 'ur.usuario_id')->on('ue.tenant_id', '=', 'ur.tenant_id');
            })
            ->join('almacenes as a', function ($join): void {
                $join->on('a.establecimiento_id', '=', 'ue.establecimiento_id')->on('a.tenant_id', '=', 'ue.tenant_id');
            })
            ->join('users as u', 'u.id', '=', 'ur.usuario_id')
            ->where('r.slug', 'almacen')
            ->whereColumn('r.tenant_id', 'ur.tenant_id')
            ->where('a.activo', true)
            ->where('u.activo', true)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')->from('usuario_almacen as ua')
                    ->whereColumn('ua.usuario_id', 'ur.usuario_id')
                    ->whereColumn('ua.tenant_id', 'ur.tenant_id');
            })
            ->select(['ur.tenant_id', 'ur.usuario_id', 'a.id as almacen_id', 'ue.asignado_por'])
            ->get();

        foreach ($assignments->chunk(500) as $chunk) {
            DB::table('usuario_almacen')->insertOrIgnore($chunk->map(fn ($row): array => [
                'tenant_id' => $row->tenant_id,
                'usuario_id' => $row->usuario_id,
                'almacen_id' => $row->almacen_id,
                'asignado_por' => $row->asignado_por,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        }
    }
}

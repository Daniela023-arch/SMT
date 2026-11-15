<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RolesGlobalesSeeder extends Seeder
{
    public function run(): void
    {
        $role = Rol::query()->firstOrCreate(
            ['tenant_id' => null, 'slug' => 'superadmin'],
            ['nombre' => 'Superadministrador', 'es_sistema' => true],
        );

        if (! $role->es_sistema) {
            throw new RuntimeException('El rol global superadmin existe, pero no está marcado como rol de sistema.');
        }

        $permissionIds = Permiso::query()->pluck('id');
        $now = now();
        $rows = $permissionIds->map(fn (int $permissionId): array => [
            'rol_id' => $role->getKey(),
            'permiso_id' => $permissionId,
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('rol_permiso')->insertOrIgnore($chunk);
        }
    }
}

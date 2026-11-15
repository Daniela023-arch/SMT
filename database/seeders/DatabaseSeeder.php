<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed reference data shared by the platform.
     *
     * Demo credentials are deliberately limited to local development.
     */
    public function run(): void
    {
        $this->call([
            CatalogosBaseSeeder::class,
            PermisosSeeder::class,
            RolesGlobalesSeeder::class,
            ConfiguracionPlataformaSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                SuperadminDemoSeeder::class,
                PlanesDemoSeeder::class,
                TenantDemoSeeder::class,
                TenantsMaquetacionSeeder::class,
                SolicitudesDemoSeeder::class,
                PromocionesDemoSeeder::class,
                UsuariosDemoSeeder::class,
                RolesTenantSeeder::class,
            ]);
        } else {
            $this->call(RolesTenantSeeder::class);
        }
    }
}

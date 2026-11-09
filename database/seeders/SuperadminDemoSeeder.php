<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use App\Services\AccesoTenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class SuperadminDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('El usuario de demostración solo se puede crear en APP_ENV=local.');
        }

        $email = env('SUPERADMIN_EMAIL', 'superadmin@multiverse.local');
        $password = env('SUPERADMIN_PASSWORD') ?: Str::random(32);
        $user = User::query()->firstOrNew(['email' => $email]);
        $created = ! $user->exists;

        if (! $created && $user->tenant_id !== null) {
            throw new RuntimeException('El correo configurado ya pertenece a un usuario de tenant. Usa otro SUPERADMIN_EMAIL.');
        }

        if ($created) {
            $user->forceFill([
                'name' => 'Superadministrador MULTIVERSE',
                'password' => $password,
                'tenant_id' => null,
                'activo' => true,
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user->forceFill(['activo' => true, 'email_verified_at' => $user->email_verified_at ?? now()])->save();
        }

        $role = Rol::query()->firstOrCreate(
            ['tenant_id' => null, 'slug' => 'superadmin'],
            ['nombre' => 'Superadministrador', 'es_sistema' => true],
        );

        app(AccesoTenantService::class)->asignarRol((int) $user->getKey(), (int) $role->getKey(), null);

        $this->command?->info('Cuenta superadmin local disponible: '.$email);
        if ($created) {
            $this->command?->warn('Contraseña temporal: '.$password);
            $this->command?->line('Guárdala ahora; se genera una nueva solo al crear una cuenta nueva.');
        } else {
            $this->command?->line('La cuenta ya existía; conserva su contraseña actual.');
        }
    }
}

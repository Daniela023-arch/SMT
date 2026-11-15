<?php

namespace Database\Seeders;

use App\Models\PlanSuscripcion;
use Illuminate\Database\Seeder;
use RuntimeException;

class PlanesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Los planes de demostración solo se pueden crear en APP_ENV=local.');
        }

        foreach ([
            ['nombre' => 'Mensual', 'periodicidad' => 'mensual'],
            ['nombre' => 'Trimestral', 'periodicidad' => 'trimestral'],
            ['nombre' => 'Anual', 'periodicidad' => 'anual'],
        ] as $plan) {
            PlanSuscripcion::query()->firstOrCreate(
                ['nombre' => $plan['nombre']],
                [
                    'precio' => 0,
                    'moneda' => 'MXN',
                    'periodicidad' => $plan['periodicidad'],
                    'limite_usuarios' => null,
                    'limite_productos' => null,
                    'activo' => true,
                ],
            );
        }

        PlanSuscripcion::query()->where('nombre', 'Demostración local')->update(['activo' => false]);
    }
}

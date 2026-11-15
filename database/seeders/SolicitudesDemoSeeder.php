<?php

namespace Database\Seeders;

use App\Models\SolicitudInventario;
use App\Models\TipoInventario;
use Illuminate\Database\Seeder;
use RuntimeException;

class SolicitudesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Las solicitudes de demostración solo se pueden crear en APP_ENV=local.');
        }

        $tipoId = TipoInventario::query()->where('nombre', 'Ferretería')->value('id');

        foreach ([
            ['nombre_negocio' => 'Ferretería Los Pinos', 'email_contacto' => 'andrea.rios@lospinos.local'],
            ['nombre_negocio' => 'Grupo Ferretero del Norte', 'email_contacto' => 'luis.marquez@ferreteronorte.local'],
            ['nombre_negocio' => 'Ferretería Industrial Oriente', 'email_contacto' => 'sofia.leon@industrialoriente.local'],
        ] as $solicitud) {
            SolicitudInventario::query()->firstOrCreate(
                ['email_contacto' => $solicitud['email_contacto']],
                [
                    ...$solicitud,
                    'tipo_inventario_id' => $tipoId,
                    'estado' => 'pendiente',
                    'observaciones' => 'Registro de demostración local para probar la revisión de solicitudes.',
                ],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\PromocionPublica;
use Illuminate\Database\Seeder;
use RuntimeException;

class PromocionesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Las promociones de demostración solo se pueden crear en APP_ENV=local.');
        }

        $promociones = [
            ['titulo' => 'Alertas de stock mínimo por almacén', 'etiqueta' => 'Nuevo', 'descripcion' => 'Identifica productos con existencias bajas en cada sucursal.', 'orden' => 1],
            ['titulo' => 'Migra tu inventario sin costo de implementación', 'etiqueta' => 'Promoción', 'descripcion' => 'Te ayudamos a importar tu catálogo desde Excel o CSV durante tu primer mes.', 'orden' => 2],
            ['titulo' => 'Control de inventarios multisucursal', 'etiqueta' => 'Webinar', 'descripcion' => 'Conoce cómo mantener el inventario conectado entre sucursales y almacenes.', 'orden' => 3],
        ];

        foreach ($promociones as $promocion) {
            PromocionPublica::query()->firstOrCreate(
                ['titulo' => $promocion['titulo']],
                [
                    ...$promocion,
                    'texto_boton' => 'Solicitar sistema',
                    'url_boton' => '/solicitar',
                    'activo' => true,
                ],
            );
        }
    }
}

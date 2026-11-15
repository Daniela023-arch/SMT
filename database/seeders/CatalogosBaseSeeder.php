<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\TipoInventario;
use Illuminate\Database\Seeder;

class CatalogosBaseSeeder extends Seeder
{
    public function run(): void
    {
        TipoInventario::query()->updateOrCreate(
            ['nombre' => 'Ferretería'],
            ['descripcion' => 'Empresa ferretera con catálogo general, sucursales, almacenes y centros de distribución.'],
        );

        $modulos = [
            'sucursales' => 'Sucursales y almacenes',
            'productos' => 'Catálogo de productos',
            'stock' => 'Control de existencias',
            'categorias' => 'Categorías y marcas',
            'ubicaciones' => 'Ubicaciones',
            'proveedores' => 'Proveedores',
            'lotes' => 'Lotes y series',
            'ventas' => 'Ventas y pagos',
            'devoluciones' => 'Devoluciones',
            'csv' => 'Importaciones CSV',
            'reportes' => 'Reportes',
            'transferencias' => 'Transferencias entre almacenes',
            'almacenes' => 'Almacenes',
            'compras' => 'Compras y recepciones',
            'cotizaciones' => 'Cotizaciones',
            'conteos' => 'Conteos de inventario',
            'usuarios' => 'Usuarios y permisos',
        ];

        foreach ($modulos as $clave => $nombre) {
            Modulo::query()->updateOrCreate(
                ['clave' => $clave],
                ['nombre' => $nombre, 'activo' => true],
            );
        }
    }
}

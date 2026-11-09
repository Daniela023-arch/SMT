<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipos_inventario')->updateOrInsert(
            ['nombre' => 'Ferretería'],
            ['descripcion' => 'Empresa ferretera con catálogo general, sucursales, almacenes y centros de distribución.', 'updated_at' => now(), 'created_at' => now()],
        );

        foreach ([
            'sucursales' => 'Sucursales y almacenes',
            'productos' => 'Catálogo de productos',
            'stock' => 'Control de existencias',
            'ventas' => 'Ventas y pagos',
            'transferencias' => 'Transferencias entre almacenes',
            'compras' => 'Compras y recepciones',
            'proveedores' => 'Proveedores',
            'reportes' => 'Reportes',
            'usuarios' => 'Usuarios y permisos',
        ] as $clave => $nombre) {
            DB::table('modulos')->updateOrInsert(
                ['clave' => $clave],
                ['nombre' => $nombre, 'activo' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    public function down(): void
    {
        foreach (['sucursales', 'productos', 'stock', 'ventas', 'transferencias', 'compras', 'proveedores', 'reportes', 'usuarios'] as $clave) {
            $moduloId = DB::table('modulos')->where('clave', $clave)->value('id');
            if ($moduloId !== null && ! DB::table('modulos_tenant')->where('modulo_id', $moduloId)->exists()) {
                DB::table('modulos')->where('id', $moduloId)->delete();
            }
        }

        $tipoId = DB::table('tipos_inventario')->where('nombre', 'Ferretería')->value('id');
        if ($tipoId !== null
            && ! DB::table('tenants')->where('tipo_inventario_id', $tipoId)->exists()
            && ! DB::table('solicitudes_inventario')->where('tipo_inventario_id', $tipoId)->exists()) {
            DB::table('tipos_inventario')->where('id', $tipoId)->delete();
        }
    }
};

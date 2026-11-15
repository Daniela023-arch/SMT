<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisosSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISOS = [
        'superadmin.dashboard.ver' => 'Consultar el panel global de superadministración.',
        'superadmin.solicitudes.gestionar' => 'Revisar, aprobar y rechazar solicitudes de registro.',
        'superadmin.tenants.gestionar' => 'Consultar y administrar negocios registrados.',
        'superadmin.planes.gestionar' => 'Crear y modificar planes y sus precios.',
        'superadmin.suscripciones.gestionar' => 'Administrar suscripciones de los negocios.',
        'superadmin.modulos.gestionar' => 'Habilitar y administrar módulos de plataforma.',
        'superadmin.usuarios.gestionar' => 'Administrar usuarios globales y sus accesos.',
        'superadmin.promociones.gestionar' => 'Administrar promociones públicas.',
        'superadmin.reportes.ver' => 'Consultar reportes globales.',
        'superadmin.configuracion.gestionar' => 'Modificar la configuración de la plataforma.',
        'tenant.establecimientos.ver' => 'Consultar sucursales y almacenes del negocio.',
        'tenant.establecimientos.gestionar' => 'Crear y modificar sucursales y almacenes.',
        'tenant.productos.ver' => 'Consultar el catálogo de productos.',
        'tenant.productos.gestionar' => 'Crear, modificar y archivar productos.',
        'tenant.inventario.ver' => 'Consultar existencias y movimientos de inventario.',
        'tenant.inventario.gestionar' => 'Registrar ajustes y movimientos de inventario.',
        'tenant.ventas.ver' => 'Consultar ventas y pagos.',
        'tenant.ventas.gestionar' => 'Registrar ventas y pagos.',
        'tenant.transferencias.ver' => 'Consultar transferencias entre ubicaciones.',
        'tenant.transferencias.gestionar' => 'Crear, aprobar, recibir o cancelar transferencias.',
        'tenant.proveedores.gestionar' => 'Administrar proveedores y sus productos.',
        'tenant.reportes.ver' => 'Consultar reportes del negocio.',
        'tenant.usuarios.gestionar' => 'Administrar usuarios y roles del negocio.',
    ];

    public function run(): void
    {
        foreach (self::PERMISOS as $clave => $descripcion) {
            Permiso::query()->updateOrCreate(
                ['clave' => $clave],
                ['descripcion' => $descripcion],
            );
        }
    }
}

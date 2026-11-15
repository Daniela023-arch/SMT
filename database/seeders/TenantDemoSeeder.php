<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\ConfiguracionInventario;
use App\Models\ConfiguracionTenant;
use App\Models\DetalleVenta;
use App\Models\Establecimiento;
use App\Models\Existencia;
use App\Models\Modulo;
use App\Models\ModuloTenant;
use App\Models\PlanSuscripcion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\StockMovimiento;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\TipoInventario;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Venta;
use App\Services\AccesoTenantService;
use App\Services\PagoVentaService;
use App\Services\TenantRoleService;
use App\Services\VentaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('El tenant y los datos de demostración solo se pueden crear en APP_ENV=local.');
        }

        $tipo = TipoInventario::query()->where('nombre', 'Ferretería')->firstOrFail();
        $plan = PlanSuscripcion::query()->where('nombre', 'Anual')->firstOrFail();

        DB::transaction(function () use ($tipo, $plan): void {
            $tenant = Tenant::query()->where('slug', 'ferreteria-alfa')->first()
                ?? Tenant::query()->where('slug', 'ferreteria-demo')->first();
            if ($tenant === null) {
                $tenant = new Tenant;
            }
            $tenant->forceFill([
                'slug' => 'ferreteria-alfa',
                'nombre' => 'Ferretería Alfa',
                'email' => 'contacto@ferreteriaalfa.mx',
                'estado' => 'activo',
                'tipo_inventario_id' => $tipo->getKey(),
            ])->save();

            ConfiguracionInventario::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey()],
                ['permitir_stock_negativo' => false, 'controlar_lotes' => false, 'controlar_caducidad' => false, 'controlar_series' => true],
            );
            ConfiguracionTenant::query()->updateOrCreate(
                ['tenant_id' => $tenant->getKey()],
                ['color_primario' => '#04724d', 'color_secundario' => '#0f172a', 'preferencias' => ['tema' => 'sistema']],
            );

            foreach (Modulo::query()->where('activo', true)->get() as $modulo) {
                ModuloTenant::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'modulo_id' => $modulo->getKey()],
                    ['habilitado' => true],
                );
            }

            $suscripcion = SuscripcionTenant::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'plan_id' => $plan->getKey()],
                ['estado' => 'activa', 'inicio_at' => now(), 'fin_at' => \Illuminate\Support\Carbon::parse('2027-08-12 23:59:59')],
            );
            if ($suscripcion->estado !== 'activa' || $suscripcion->fin_at?->isPast()) {
                $suscripcion->update(['estado' => 'activa', 'inicio_at' => now(), 'fin_at' => \Illuminate\Support\Carbon::parse('2027-08-12 23:59:59')]);
            }
            SuscripcionTenant::query()
                ->where('tenant_id', $tenant->getKey())
                ->whereHas('plan', fn ($query) => $query->where('nombre', 'Demostración local'))
                ->update(['estado' => 'cancelada', 'cancelada_at' => now()]);

            $roles = $this->crearRoles($tenant);
            [$usuarios, $password, $usuariosNuevos] = $this->crearUsuarios($tenant, $roles);

            $centro = Establecimiento::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'codigo' => 'CENTRO'],
                ['nombre' => 'Sucursal Centro', 'tipo' => 'sucursal', 'direccion' => 'Ciudad de México', 'activo' => true],
            );
            $cedis = Establecimiento::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'codigo' => 'CEDIS'],
                ['nombre' => 'Centro de distribución', 'tipo' => 'centro_distribucion', 'direccion' => 'Parque industrial, Bodega 4', 'activo' => true],
            );

            $almacenCentro = $this->crearAlmacen($tenant, $centro, 'CENTRAL', 'Almacén principal');
            $almacenCedis = $this->crearAlmacen($tenant, $cedis, 'GENERAL', 'Almacén general');
            $ubicacionCentro = $almacenCentro->ubicaciones()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'codigo' => 'CENTRAL-PRINCIPAL'],
                ['nombre' => 'Ubicación principal'],
            );
            $ubicacionCedis = $almacenCedis->ubicaciones()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'codigo' => 'GENERAL-PRINCIPAL'],
                ['nombre' => 'Ubicación principal'],
            );

            $sucursalesPorUsuario = [
                'admin' => [$centro, $cedis],
                'gerente' => [$centro, $cedis],
                'almacen' => [$centro, $cedis],
                'ventas' => [$centro],
            ];
            foreach ($sucursalesPorUsuario as $slug => $sucursales) {
                DB::table('usuario_establecimiento')->where('tenant_id', $tenant->getKey())->where('usuario_id', $usuarios[$slug]->getKey())->delete();
                DB::table('usuario_almacen')->where('tenant_id', $tenant->getKey())->where('usuario_id', $usuarios[$slug]->getKey())->delete();
                foreach ($sucursales as $establecimiento) {
                    DB::table('usuario_establecimiento')->updateOrInsert(
                        ['usuario_id' => $usuarios[$slug]->getKey(), 'establecimiento_id' => $establecimiento->getKey()],
                        ['tenant_id' => $tenant->getKey(), 'asignado_por' => $usuarios['admin']->getKey(), 'created_at' => now(), 'updated_at' => now()],
                    );
                }
            }
            foreach ([$almacenCentro, $almacenCedis] as $almacenAsignado) {
                DB::table('usuario_almacen')->updateOrInsert(
                    ['usuario_id' => $usuarios['almacen']->getKey(), 'almacen_id' => $almacenAsignado->getKey()],
                    ['tenant_id' => $tenant->getKey(), 'asignado_por' => $usuarios['admin']->getKey(), 'created_at' => now(), 'updated_at' => now()],
                );
            }
            DB::table('usuario_almacen')->updateOrInsert(
                ['usuario_id' => $usuarios['ventas']->getKey(), 'almacen_id' => $almacenCentro->getKey()],
                ['tenant_id' => $tenant->getKey(), 'asignado_por' => $usuarios['admin']->getKey(), 'created_at' => now(), 'updated_at' => now()],
            );

            $productos = $this->crearCatalogo($tenant);
            $proveedor = Proveedor::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'nombre' => 'Suministros Ferreteros Demo'],
                ['rfc' => 'SFD260101AA1', 'email' => 'ventas@suministros-demo.local', 'telefono' => '5550001122', 'direccion' => 'Zona comercial, Ciudad de México'],
            );

            foreach ($productos as $producto) {
                DB::table('producto_proveedor')->updateOrInsert(
                    ['tenant_id' => $tenant->getKey(), 'producto_id' => $producto->getKey(), 'proveedor_id' => $proveedor->getKey()],
                    ['codigo_proveedor' => 'PROV-'.$producto->sku, 'costo_referencia' => max(1, (float) $producto->precio_venta * 0.65), 'created_at' => now(), 'updated_at' => now()],
                );

                [$ubicacionNombre, $ubicacionCodigo, $existencias] = match ($producto->sku) {
                    'FER-001' => ['Rack A-02', 'RACK-A-02', 32],
                    'FER-002' => ['Pasillo 3', 'PASILLO-3', 145],
                    'FER-003' => ['Patio de materiales', 'PATIO-MATERIALES', 420],
                    'FER-004' => ['Gaveta T-12', 'GAVETA-T-12', 3600],
                    'FER-005' => ['Pasillo 8', 'PASILLO-8', 14],
                    default => ['Rack E-04', 'RACK-E-04', 980],
                };
                $ubicacionProducto = $almacenCentro->ubicaciones()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'codigo' => $ubicacionCodigo],
                    ['nombre' => $ubicacionNombre],
                );

                $existencia = Existencia::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'almacen_id' => $almacenCentro->getKey(), 'producto_id' => $producto->getKey(), 'ubicacion_id' => $ubicacionProducto->getKey(), 'lote_id' => null],
                    ['cantidad' => $existencias],
                );

                if ($existencia->wasRecentlyCreated && $existencias > 0) {
                    StockMovimiento::query()->firstOrCreate(
                        ['tenant_id' => $tenant->getKey(), 'referencia_tipo' => self::class, 'referencia_id' => $producto->getKey(), 'tipo' => 'entrada', 'motivo' => 'Existencia inicial de demostración'],
                        ['producto_id' => $producto->getKey(), 'cantidad' => $existencias, 'usuario_id' => $usuarios['admin']->getKey(), 'ubicacion_destino_id' => $ubicacionProducto->getKey(), 'almacen_destino_id' => $almacenCentro->getKey()],
                    );
                }
            }

            $this->crearVentaDemo($tenant, $centro, $almacenCentro, $ubicacionCentro, $productos['FER-004'], $usuarios['ventas']);
            $this->crearTransferenciaDemo($tenant, $centro, $cedis, $almacenCentro, $almacenCedis, $productos['FER-003'], $ubicacionCentro, $ubicacionCedis, $usuarios['almacen']);

            if ($usuariosNuevos !== []) {
                $this->command?->warn('Contraseña temporal de las cuentas de ferretería recién creadas: '.$password);
                $this->command?->line('Usuarios nuevos: '.implode(', ', $usuariosNuevos).'. Las cuentas que ya existían conservan su contraseña.');
            } else {
                $this->command?->info('Las cuentas demo de la ferretería ya existían; se conservaron sus contraseñas.');
            }
        });
    }

    /** @return array{admin: Rol, gerente: Rol, ventas: Rol, almacen: Rol} */
    private function crearRoles(Tenant $tenant): array
    {
        return app(TenantRoleService::class)->inicializar($tenant);
    }

    /** @return array{0: array{admin: User, gerente: User, ventas: User, almacen: User}, 1: string, 2: list<string>} */
    private function crearUsuarios(Tenant $tenant, array $roles): array
    {
        $password = env('DEMO_TENANT_PASSWORD') ?: 'demo1234';
        $definitions = [
            'admin' => ['name' => 'Administración Ferretería Alfa', 'email' => 'admin@ferreteriaalfa.mx'],
            'gerente' => ['name' => 'Gerencia Ferretería Alfa', 'email' => 'gerente@ferreteriaalfa.mx'],
            'ventas' => ['name' => 'Encargado de ventas Alfa', 'email' => 'ventas@ferreteriaalfa.mx'],
            'almacen' => ['name' => 'Encargado de almacén Alfa', 'email' => 'almacen@ferreteriaalfa.mx'],
        ];
        $usuarios = [];
        $nuevos = [];

        foreach ($definitions as $slug => $data) {
            $usuario = User::query()->firstOrNew(['email' => $data['email']]);
            if ($usuario->exists && (int) $usuario->tenant_id !== (int) $tenant->getKey()) {
                throw new RuntimeException('El correo '.$data['email'].' ya está asociado a otro tenant.');
            }
            if (! $usuario->exists) {
                $usuario->forceFill([
                    ...$data,
                    'tenant_id' => $tenant->getKey(),
                    'password' => $password,
                    'activo' => true,
                    'email_verified_at' => now(),
                ])->save();
                $nuevos[] = $data['email'];
            } else {
                $usuario->forceFill(['activo' => true, 'email_verified_at' => $usuario->email_verified_at ?? now()])->save();
            }

            DB::table('usuario_rol')->where('usuario_id', $usuario->getKey())->where('tenant_id', $tenant->getKey())->delete();
            app(AccesoTenantService::class)->asignarRol((int) $usuario->getKey(), (int) $roles[$slug]->getKey(), (int) $tenant->getKey());
            $usuarios[$slug] = $usuario;
        }

        return [$usuarios, $password, $nuevos];
    }

    private function crearAlmacen(Tenant $tenant, Establecimiento $establecimiento, string $codigo, string $nombre): Almacen
    {
        return Almacen::query()->firstOrCreate(
            ['establecimiento_id' => $establecimiento->getKey(), 'codigo' => $codigo],
            ['tenant_id' => $tenant->getKey(), 'nombre' => $nombre, 'activo' => true],
        );
    }

    /** @return array<string, Producto> */
    private function crearCatalogo(Tenant $tenant): array
    {
        $definiciones = [
            ['sku' => 'FER-001', 'nombre' => 'Taladro inalámbrico 20 V', 'categoria' => 'Herramientas eléctricas', 'unidad_medida' => 'pieza', 'precio_venta' => 1899, 'stock_minimo' => 8, 'requiere_serie' => false],
            ['sku' => 'FER-002', 'nombre' => 'Martillo de uña 16 oz', 'categoria' => 'Herramientas manuales', 'unidad_medida' => 'pieza', 'precio_venta' => 189, 'stock_minimo' => 25, 'requiere_serie' => false],
            ['sku' => 'FER-003', 'nombre' => 'Cemento gris 50 kg', 'categoria' => 'Materiales de construcción', 'unidad_medida' => 'saco', 'precio_venta' => 238, 'stock_minimo' => 80, 'requiere_serie' => false],
            ['sku' => 'FER-004', 'nombre' => 'Tornillo hexagonal 1/4', 'categoria' => 'Tornillería', 'unidad_medida' => 'pieza', 'precio_venta' => 3.50, 'stock_minimo' => 500, 'requiere_serie' => false],
            ['sku' => 'FER-005', 'nombre' => 'Pintura vinílica blanca 19 L', 'categoria' => 'Pinturas', 'unidad_medida' => 'cubeta', 'precio_venta' => 1190, 'stock_minimo' => 20, 'requiere_serie' => false],
            ['sku' => 'FER-006', 'nombre' => 'Cable THW calibre 12 (metro)', 'categoria' => 'Material eléctrico', 'unidad_medida' => 'metro', 'precio_venta' => 22, 'stock_minimo' => 150, 'requiere_serie' => false],
        ];
        $productos = [];

        foreach ($definiciones as $definition) {
            $categoria = Categoria::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'nombre' => $definition['categoria']],
                ['categoria_padre_id' => null],
            );
            $sku = $definition['sku'];
            unset($definition['sku'], $definition['categoria']);
            $productos[$sku] = Producto::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'sku' => $sku],
                ['categoria_id' => $categoria->getKey(), 'descripcion' => 'Artículo de demostración local.', 'activo' => true, ...$definition],
            );
        }

        return $productos;
    }

    private function crearVentaDemo(Tenant $tenant, Establecimiento $establecimiento, Almacen $almacen, Ubicacion $ubicacion, Producto $producto, User $usuario): void
    {
        $cantidad = 4;
        $total = round((float) $producto->precio_venta * $cantidad, 2);
        $venta = Venta::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'folio' => 'DEMO-VTA-0001'],
            ['establecimiento_id' => $establecimiento->getKey(), 'almacen_id' => $almacen->getKey(), 'usuario_id' => $usuario->getKey(), 'estado' => 'borrador', 'subtotal' => $total, 'impuestos' => 0, 'descuento' => 0, 'total' => $total],
        );

        if ($venta->estado === 'borrador') {
            DetalleVenta::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'venta_id' => $venta->getKey(), 'producto_id' => $producto->getKey()],
                ['cantidad' => $cantidad, 'precio_unitario' => $producto->precio_venta, 'descuento' => 0, 'impuesto' => 0, 'total' => $total],
            );
            app(VentaService::class)->confirmar($venta);
        }

        if (! $venta->pagos()->where('referencia', 'DEMO-PAGO-0001')->exists()) {
            app(PagoVentaService::class)->registrar((int) $venta->getKey(), (string) $total, 'efectivo', 'DEMO-PAGO-0001');
        }
    }

    private function crearTransferenciaDemo(Tenant $tenant, Establecimiento $origen, Establecimiento $destino, Almacen $almacenOrigen, Almacen $almacenDestino, Producto $producto, Ubicacion $ubicacionOrigen, Ubicacion $ubicacionDestino, User $usuario): void
    {
        $transferencia = Transferencia::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'folio' => 'DEMO-TRF-0001'],
            ['establecimiento_origen_id' => $origen->getKey(), 'establecimiento_destino_id' => $destino->getKey(), 'almacen_origen_id' => $almacenOrigen->getKey(), 'almacen_destino_id' => $almacenDestino->getKey(), 'solicitante_id' => $usuario->getKey(), 'folio' => 'DEMO-TRF-0001', 'estado' => 'solicitada', 'motivo' => 'Transferencia de demostración local.', 'solicitada_at' => now()],
        );
        TransferenciaDetalle::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'transferencia_id' => $transferencia->getKey(), 'producto_id' => $producto->getKey()],
            ['ubicacion_origen_id' => $ubicacionOrigen->getKey(), 'ubicacion_destino_id' => $ubicacionDestino->getKey(), 'cantidad_solicitada' => 5, 'cantidad_enviada' => 0, 'cantidad_recibida' => 0],
        );
    }
}

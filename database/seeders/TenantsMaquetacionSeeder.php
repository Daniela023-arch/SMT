<?php

namespace Database\Seeders;

use App\Models\ConfiguracionInventario;
use App\Models\ConfiguracionTenant;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Establecimiento;
use App\Models\Existencia;
use App\Models\Modulo;
use App\Models\ModuloTenant;
use App\Models\PlanSuscripcion;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\TipoInventario;
use App\Models\User;
use App\Models\Ubicacion;
use App\Services\AccesoTenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantsMaquetacionSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Los tenants de demostración solo se pueden crear en APP_ENV=local.');
        }

        $tipoId = TipoInventario::query()->where('nombre', 'Ferretería')->value('id');
        $password = env('DEMO_TENANT_PASSWORD') ?: 'demo1234';
        $tenants = [
            [
                'slug' => 'ferreteria-beta', 'nombre' => 'Ferretería Beta', 'email' => 'contacto@ferreteriabeta.mx',
                'color' => '#315c73', 'plan' => 'Trimestral', 'fin' => '2027-01-15 23:59:59', 'admin' => ['name' => 'Administración Ferretería Beta', 'email' => 'admin@ferreteriabeta.mx'],
            ],
            [
                'slug' => 'grupo-ferretero-central', 'nombre' => 'Grupo Ferretero Central', 'email' => 'contacto@grupoffereterocentral.mx',
                'color' => '#704f3e', 'plan' => 'Mensual', 'fin' => '2026-11-30 23:59:59', 'admin' => null,
            ],
        ];

        foreach ($tenants as $data) {
            DB::transaction(function () use ($data, $tipoId, $password): void {
                $tenant = Tenant::query()->firstOrCreate(
                    ['slug' => $data['slug']],
                    ['nombre' => $data['nombre'], 'email' => $data['email'], 'estado' => 'activo', 'tipo_inventario_id' => $tipoId],
                );
                ConfiguracionInventario::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey()],
                    ['permitir_stock_negativo' => false, 'controlar_lotes' => false, 'controlar_caducidad' => false, 'controlar_series' => false],
                );
                ConfiguracionTenant::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey()],
                    ['color_primario' => $data['color'], 'color_secundario' => '#0f172a', 'preferencias' => ['tema' => 'sistema']],
                );

                foreach (Modulo::query()->where('activo', true)->get() as $modulo) {
                    ModuloTenant::query()->updateOrCreate(
                        ['tenant_id' => $tenant->getKey(), 'modulo_id' => $modulo->getKey()],
                        ['habilitado' => ! ($tenant->slug === 'grupo-ferretero-central' && $modulo->clave === 'lotes')],
                    );
                }

                $plan = PlanSuscripcion::query()->where('nombre', $data['plan'])->firstOrFail();
                $suscripcion = SuscripcionTenant::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'plan_id' => $plan->getKey()],
                    ['estado' => 'activa', 'inicio_at' => now(), 'fin_at' => \Illuminate\Support\Carbon::parse($data['fin'])],
                );
                if ($suscripcion->estado !== 'activa' || $suscripcion->fin_at?->isPast()) {
                    $suscripcion->update(['estado' => 'activa', 'inicio_at' => now(), 'fin_at' => \Illuminate\Support\Carbon::parse($data['fin'])]);
                }

                $establecimiento = Establecimiento::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'codigo' => 'MATRIZ'],
                    ['nombre' => 'Sucursal Matriz', 'tipo' => 'sucursal', 'direccion' => $tenant->nombre, 'activo' => true],
                );
                if ($data['admin'] === null) {
                    $this->crearCatalogoDemo($tenant, $establecimiento);
                    return;
                }

                $usuario = User::query()->firstOrNew(['email' => $data['admin']['email']]);
                if ($usuario->exists && (int) $usuario->tenant_id !== (int) $tenant->getKey()) {
                    throw new RuntimeException('El correo '.$data['admin']['email'].' ya pertenece a otro tenant.');
                }
                if (! $usuario->exists) {
                    $usuario->forceFill([
                        ...$data['admin'],
                        'tenant_id' => $tenant->getKey(),
                        'password' => $password,
                        'activo' => true,
                        'email_verified_at' => now(),
                    ])->save();
                    $this->command?->line('Cuenta de demostración creada: '.$data['admin']['email']);
                } else {
                    $usuario->forceFill(['activo' => true, 'email_verified_at' => $usuario->email_verified_at ?? now()])->save();
                }

                $rol = Rol::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'slug' => 'admin'],
                    ['nombre' => 'Administrador', 'es_sistema' => false],
                );
                app(AccesoTenantService::class)->asignarRol((int) $usuario->getKey(), (int) $rol->getKey(), (int) $tenant->getKey());
                DB::table('usuario_establecimiento')->updateOrInsert(
                    ['usuario_id' => $usuario->getKey(), 'establecimiento_id' => $establecimiento->getKey()],
                    ['tenant_id' => $tenant->getKey(), 'asignado_por' => $usuario->getKey(), 'created_at' => now(), 'updated_at' => now()],
                );
                $this->crearCatalogoDemo($tenant, $establecimiento);
            });
        }

        $this->seedSuspendedAccount($password);
    }

    private function seedSuspendedAccount(string $password): void
    {
        $usuario = User::query()->firstOrNew(['email' => 'suspendida@ejemplo.mx']);
        if (! $usuario->exists) {
            $usuario->forceFill([
                'name' => 'Cuenta suspendida de demostración',
                'password' => $password,
                'tenant_id' => null,
                'activo' => false,
                'email_verified_at' => now(),
            ])->save();
        } else {
            $usuario->forceFill(['activo' => false])->save();
        }
    }

    private function crearCatalogoDemo(Tenant $tenant, Establecimiento $establecimiento): void
    {
        $almacen = Almacen::query()->firstOrCreate(
            ['establecimiento_id' => $establecimiento->getKey(), 'codigo' => 'MATRIZ-ALM'],
            ['tenant_id' => $tenant->getKey(), 'nombre' => 'Almacén matriz', 'activo' => true],
        );
        $catalogo = [
            ['FER-001', 'Taladro inalámbrico 20 V', 'Herramientas eléctricas', 'pieza', 1899, 8, 32, 'Rack A-02'],
            ['FER-002', 'Martillo de uña 16 oz', 'Herramientas manuales', 'pieza', 189, 25, 145, 'Pasillo 3'],
            ['FER-003', 'Cemento gris 50 kg', 'Materiales de construcción', 'saco', 238, 80, 420, 'Patio de materiales'],
            ['FER-004', 'Tornillo hexagonal 1/4', 'Tornillería', 'pieza', 3.50, 500, 3600, 'Gaveta T-12'],
            ['FER-005', 'Pintura vinílica blanca 19 L', 'Pinturas', 'cubeta', 1190, 20, 14, 'Pasillo 8'],
            ['FER-006', 'Cable THW calibre 12 (metro)', 'Material eléctrico', 'metro', 22, 150, 980, 'Rack E-04'],
        ];

        foreach ($catalogo as [$sku, $nombre, $nombreCategoria, $unidad, $precio, $minimo, $cantidad, $nombreUbicacion]) {
            $categoria = Categoria::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'nombre' => $nombreCategoria],
                ['categoria_padre_id' => null],
            );
            $producto = Producto::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'sku' => $sku],
                ['categoria_id' => $categoria->getKey(), 'nombre' => $nombre, 'descripcion' => 'Producto de ejemplo de la maqueta MULTIVERSE.', 'unidad_medida' => $unidad, 'precio_venta' => $precio, 'stock_minimo' => $minimo, 'requiere_serie' => false, 'activo' => true],
            );
            $codigoUbicacion = str($nombreUbicacion)->slug()->upper()->toString();
            $ubicacion = Ubicacion::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'almacen_id' => $almacen->getKey(), 'codigo' => $codigoUbicacion],
                ['nombre' => $nombreUbicacion],
            );
            Existencia::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'almacen_id' => $almacen->getKey(), 'producto_id' => $producto->getKey(), 'ubicacion_id' => $ubicacion->getKey(), 'lote_id' => null],
                ['cantidad' => $cantidad],
            );
        }
    }
}

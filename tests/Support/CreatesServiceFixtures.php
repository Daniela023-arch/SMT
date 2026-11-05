<?php

namespace Tests\Support;

use App\Models\ConfiguracionInventario;
use App\Models\Existencia;
use App\Models\Establecimiento;
use App\Models\Rol;
use App\Models\PlanSuscripcion;
use App\Models\Producto;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\TipoInventario;
use App\Models\Ubicacion;
use App\Models\UnidadInventario;
use App\Models\User;
use App\Services\EstablecimientoService;
use Illuminate\Support\Facades\DB;

trait CreatesServiceFixtures
{
    protected function serviceTenant(string $type = 'Abarrotes'): Tenant
    {
        $inventoryType = TipoInventario::query()->create(['nombre' => $type]);

        $tenant = Tenant::query()->create([
            'nombre' => 'Tenant '.$type,
            'slug' => 'tenant-'.strtolower($type).'-'.uniqid(),
            'estado' => 'activo',
            'tipo_inventario_id' => $inventoryType->getKey(),
        ]);

        ConfiguracionInventario::query()->create(['tenant_id' => $tenant->getKey()]);

        return $tenant;
    }

    protected function servicePlan(array $attributes = []): PlanSuscripcion
    {
        return PlanSuscripcion::query()->create(array_merge([
            'nombre' => 'Plan '.uniqid(),
            'precio' => 100,
            'moneda' => 'MXN',
            'periodicidad' => 'mensual',
            'limite_usuarios' => 10,
            'limite_productos' => 10,
            'activo' => true,
        ], $attributes));
    }

    protected function serviceSubscription(Tenant $tenant, ?PlanSuscripcion $plan = null, array $attributes = []): SuscripcionTenant
    {
        $plan ??= $this->servicePlan();

        return SuscripcionTenant::query()->create(array_merge([
            'tenant_id' => $tenant->getKey(),
            'plan_id' => $plan->getKey(),
            'estado' => 'activa',
            'inicio_at' => now()->subDay(),
            'fin_at' => now()->addMonth(),
        ], $attributes));
    }

    protected function serviceLocation(Tenant $tenant, string $name = 'Almacén'): Ubicacion
    {
        return Ubicacion::query()->create([
            'tenant_id' => $tenant->getKey(),
            'nombre' => $name,
            'codigo' => strtoupper(substr(uniqid(), -6)),
        ]);
    }

    protected function serviceProduct(Tenant $tenant, array $attributes = []): Producto
    {
        return Producto::query()->create(array_merge([
            'tenant_id' => $tenant->getKey(),
            'sku' => 'SKU-'.uniqid(),
            'nombre' => 'Producto de prueba',
            'unidad_medida' => 'pieza',
            'stock_minimo' => 0,
            'activo' => true,
        ], $attributes));
    }

    protected function serviceUnit(Tenant $tenant, Producto $producto, Ubicacion $ubicacion, array $attributes = []): UnidadInventario
    {
        return UnidadInventario::query()->create(array_merge([
            'tenant_id' => $tenant->getKey(),
            'producto_id' => $producto->getKey(),
            'ubicacion_id' => $ubicacion->getKey(),
            'estado' => 'disponible',
        ], $attributes));
    }

    protected function serviceEstablishment(Tenant $tenant, string $type = 'sucursal', ?string $code = null): Establecimiento
    {
        return app(EstablecimientoService::class)->crear($tenant->getKey(), [
            'codigo' => $code ?? strtoupper(substr(uniqid(), -6)),
            'nombre' => 'Establecimiento de prueba',
            'tipo' => $type,
        ]);
    }

    protected function serviceWarehouse(Tenant $tenant, Establecimiento $establishment, ?string $code = null): \App\Models\Almacen
    {
        return app(EstablecimientoService::class)->crearAlmacen(
            $tenant->getKey(),
            $establishment->getKey(),
            $code ?? strtoupper(substr(uniqid(), -6)),
            'Almacén de prueba',
        );
    }

    protected function serviceRoleUser(Tenant $tenant, string $slug): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
        $role = Rol::query()->create([
            'tenant_id' => $tenant->getKey(),
            'nombre' => ucfirst($slug),
            'slug' => $slug,
            'es_sistema' => false,
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => $user->getKey(),
            'rol_id' => $role->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        return $user;
    }
}

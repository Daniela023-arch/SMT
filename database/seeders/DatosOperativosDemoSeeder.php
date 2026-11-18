<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\CodigoIdentificacion;
use App\Models\DetalleVenta;
use App\Models\Establecimiento;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Tenant;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Venta;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Services\InventarioService;
use App\Services\PagoVentaService;
use App\Services\VentaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Extra interconnected operational fixtures for the local demo tenant. */
class DatosOperativosDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Los datos operativos de demostración solo se pueden crear en APP_ENV=local.');
        }

        $tenant = Tenant::query()->where('slug', 'ferreteria-alfa')->firstOrFail();
        $branch = Establecimiento::query()->where('tenant_id', $tenant->getKey())->where('codigo', 'CENTRO')->firstOrFail();
        $destinationBranch = Establecimiento::query()->where('tenant_id', $tenant->getKey())->where('codigo', 'CEDIS')->firstOrFail();
        $warehouse = Almacen::query()->where('tenant_id', $tenant->getKey())->where('establecimiento_id', $branch->getKey())->where('codigo', 'CENTRAL')->firstOrFail();
        $destinationWarehouse = Almacen::query()->where('tenant_id', $tenant->getKey())->where('establecimiento_id', $destinationBranch->getKey())->where('codigo', 'GENERAL')->firstOrFail();
        $admin = User::query()->where('tenant_id', $tenant->getKey())->where('email', 'admin@ferreteriaalfa.mx')->firstOrFail();
        $seller = User::query()->where('tenant_id', $tenant->getKey())->where('email', 'ventas@ferreteriaalfa.mx')->firstOrFail();
        $operator = User::query()->where('tenant_id', $tenant->getKey())->where('email', 'almacen@ferreteriaalfa.mx')->firstOrFail();

        DB::transaction(function () use ($tenant, $branch, $warehouse, $destinationBranch, $destinationWarehouse, $admin, $seller, $operator): void {
            $inventory = app(InventarioService::class);
            $sales = app(VentaService::class);
            $payments = app(PagoVentaService::class);
            $destinationLocation = Ubicacion::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'almacen_id' => $destinationWarehouse->getKey(), 'codigo' => 'DEMO-RECEPCION'],
                ['nombre' => 'Recepción de demostración', 'ubicacion_padre_id' => null],
            );

            foreach ($this->products() as $index => $data) {
                $category = Categoria::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'nombre' => $data['category']],
                    ['categoria_padre_id' => null],
                );
                $product = Producto::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'sku' => $data['sku']],
                    [
                        'categoria_id' => $category->getKey(),
                        'nombre' => $data['name'],
                        'descripcion' => 'Producto adicional de demostración local.',
                        'unidad_medida' => $data['unit'],
                        'precio_venta' => $data['price'],
                        'stock_minimo' => 5,
                        'requiere_serie' => false,
                        'activo' => true,
                    ],
                );

                $provider = Proveedor::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'nombre' => $data['provider']],
                    [
                        'rfc' => sprintf('DME%02d0101AAA', $index + 1),
                        'email' => sprintf('ventas%02d@proveedor-demo.local', $index + 1),
                        'telefono' => sprintf('555000%04d', $index + 1),
                        'direccion' => 'Zona de proveedores, Ciudad de México',
                    ],
                );
                DB::table('producto_proveedor')->updateOrInsert(
                    ['tenant_id' => $tenant->getKey(), 'producto_id' => $product->getKey(), 'proveedor_id' => $provider->getKey()],
                    ['codigo_proveedor' => 'PROV-'.$data['sku'], 'costo_referencia' => round((float) $data['price'] * 0.65, 2), 'created_at' => now(), 'updated_at' => now()],
                );

                $location = Ubicacion::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'almacen_id' => $warehouse->getKey(), 'codigo' => 'DEMO-RACK-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                    ['nombre' => $data['location'], 'ubicacion_padre_id' => null],
                );
                CodigoIdentificacion::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'tipo' => 'ean13', 'codigo' => sprintf('7501234567%03d', $index + 1)],
                    ['producto_id' => $product->getKey(), 'unidad_inventario_id' => null],
                );

                $existencia = Existencia::query()->firstOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'almacen_id' => $warehouse->getKey(), 'producto_id' => $product->getKey(), 'ubicacion_id' => $location->getKey(), 'lote_id' => null],
                    ['cantidad' => 0],
                );
                if ($existencia->wasRecentlyCreated) {
                    $inventory->registrarEntrada(
                        (int) $tenant->getKey(), (int) $product->getKey(), (int) $location->getKey(), (float) (30 + $index),
                        usuarioId: (int) $admin->getKey(), motivo: 'Existencia inicial de demostración ampliada',
                        referenciaTipo: self::class, referenciaId: (int) $product->getKey(), tipo: 'entrada', almacenDestinoId: (int) $warehouse->getKey(),
                    );
                }

                $this->crearVenta($tenant, $branch, $warehouse, $seller, $product, $sales, $payments, $index);
                $this->crearTransferencia($tenant, $branch, $destinationBranch, $warehouse, $destinationLocation, $operator, $product, $location, $index);
            }
        });

        $this->command?->info('Datos demo adicionales listos: 10 productos, categorías, proveedores, códigos, ubicaciones, entradas de inventario, ventas y transferencias.');
    }

    /** @return list<array{sku: string, name: string, category: string, unit: string, price: float, provider: string, location: string}> */
    private function products(): array
    {
        return [
            ['sku' => 'DEMO-001', 'name' => 'Cinta métrica 5 m', 'category' => 'Medición y trazado', 'unit' => 'pieza', 'price' => 89.00, 'provider' => 'Herramientas del Centro', 'location' => 'Rack demostración 01'],
            ['sku' => 'DEMO-002', 'name' => 'Pinza de presión 10 pulgadas', 'category' => 'Sujeción y corte', 'unit' => 'pieza', 'price' => 245.00, 'provider' => 'Aceros y Herramientas MX', 'location' => 'Rack demostración 02'],
            ['sku' => 'DEMO-003', 'name' => 'Broca para concreto 3/8', 'category' => 'Brocas y accesorios', 'unit' => 'pieza', 'price' => 58.50, 'provider' => 'Suministros Industriales Uno', 'location' => 'Rack demostración 03'],
            ['sku' => 'DEMO-004', 'name' => 'Guantes de carnaza', 'category' => 'Seguridad industrial', 'unit' => 'par', 'price' => 112.00, 'provider' => 'Protección Laboral', 'location' => 'Rack demostración 04'],
            ['sku' => 'DEMO-005', 'name' => 'Disco de corte 4.5 pulgadas', 'category' => 'Discos abrasivos', 'unit' => 'pieza', 'price' => 24.00, 'provider' => 'Abrasivos del Valle', 'location' => 'Rack demostración 05'],
            ['sku' => 'DEMO-006', 'name' => 'Llave ajustable 10 pulgadas', 'category' => 'Plomería y llaves', 'unit' => 'pieza', 'price' => 179.00, 'provider' => 'Conexiones y Válvulas', 'location' => 'Rack demostración 06'],
            ['sku' => 'DEMO-007', 'name' => 'Extensión eléctrica 5 m', 'category' => 'Cables y extensiones', 'unit' => 'pieza', 'price' => 198.00, 'provider' => 'Eléctrica Nacional', 'location' => 'Rack demostración 07'],
            ['sku' => 'DEMO-008', 'name' => 'Silicón transparente 300 ml', 'category' => 'Adhesivos y selladores', 'unit' => 'cartucho', 'price' => 74.00, 'provider' => 'Químicos para Construcción', 'location' => 'Rack demostración 08'],
            ['sku' => 'DEMO-009', 'name' => 'Candado laminado 50 mm', 'category' => 'Cerrajería y seguridad', 'unit' => 'pieza', 'price' => 155.00, 'provider' => 'Seguridad y Herrajes', 'location' => 'Rack demostración 09'],
            ['sku' => 'DEMO-010', 'name' => 'Nivel de aluminio 24 pulgadas', 'category' => 'Niveles y medición', 'unit' => 'pieza', 'price' => 229.00, 'provider' => 'Instrumentos de Medición', 'location' => 'Rack demostración 10'],
        ];
    }

    private function crearVenta(Tenant $tenant, Establecimiento $branch, Almacen $warehouse, User $seller, Producto $product, VentaService $sales, PagoVentaService $payments, int $index): void
    {
        $folio = sprintf('DEMO-VTA-EXTRA-%04d', $index + 1);
        $quantity = 1;
        $total = round((float) $product->precio_venta * $quantity, 2);
        $sale = Venta::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'folio' => $folio],
            ['establecimiento_id' => $branch->getKey(), 'almacen_id' => $warehouse->getKey(), 'usuario_id' => $seller->getKey(), 'estado' => 'borrador', 'subtotal' => $total, 'impuestos' => 0, 'descuento' => 0, 'total' => $total],
        );

        if ($sale->estado === 'borrador') {
            DetalleVenta::query()->firstOrCreate(
                ['tenant_id' => $tenant->getKey(), 'venta_id' => $sale->getKey(), 'producto_id' => $product->getKey()],
                ['cantidad' => $quantity, 'precio_unitario' => $product->precio_venta, 'descuento' => 0, 'impuesto' => 0, 'total' => $total],
            );
            $sales->confirmar($sale);
        }

        if (! $sale->pagos()->where('referencia', $folio.'-PAGO')->exists()) {
            $payments->registrar((int) $sale->getKey(), (string) $total, $index % 2 === 0 ? 'efectivo' : 'transferencia', $folio.'-PAGO');
        }

        $sale->forceFill(['created_at' => now()->subDays($index % 10)])->save();
    }

    private function crearTransferencia(Tenant $tenant, Establecimiento $origin, Establecimiento $destination, Almacen $originWarehouse, Ubicacion $destinationLocation, User $operator, Producto $product, Ubicacion $location, int $index): void
    {
        $folio = sprintf('DEMO-TRF-EXTRA-%04d', $index + 1);
        $transfer = Transferencia::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'folio' => $folio],
            [
                'establecimiento_origen_id' => $origin->getKey(),
                'establecimiento_destino_id' => $destination->getKey(),
                'almacen_origen_id' => $originWarehouse->getKey(),
                'almacen_destino_id' => $destinationWarehouse->getKey(),
                'solicitante_id' => $operator->getKey(),
                'estado' => 'solicitada',
                'motivo' => 'Solicitud de transferencia para demostración local.',
                'solicitada_at' => now()->subDays($index % 10),
            ],
        );
        TransferenciaDetalle::query()->firstOrCreate(
            ['tenant_id' => $tenant->getKey(), 'transferencia_id' => $transfer->getKey(), 'producto_id' => $product->getKey()],
            ['ubicacion_origen_id' => $location->getKey(), 'ubicacion_destino_id' => $destinationLocation->getKey(), 'cantidad_solicitada' => 2, 'cantidad_enviada' => 0, 'cantidad_recibida' => 0],
        );
    }
}

<?php

namespace App\Livewire\Ventas;

use App\Models\Almacen;
use App\Models\DetalleVenta;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\UnidadInventario;
use App\Models\Venta;
use App\Services\PagoVentaService;
use App\Services\VentaService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ventas | MULTIVERSE')]
class Index extends Component
{
    use WithPagination;

    #[Locked]
    public int $establecimiento;

    #[Locked]
    public int $almacen;

    public array $lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];

    public int $ventaPagoId = 0;

    public string $montoPago = '';

    public string $metodoPago = 'efectivo';

    public string $referenciaPago = '';

    public function mount(int $establecimiento, int $almacen): void
    {
        $this->establecimiento = $establecimiento;
        $this->almacen = $almacen;
    }

    public function agregarLinea(): void
    {
        $this->lineas[] = ['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => ''];
    }

    public function quitarLinea(int $indice): void
    {
        if (count($this->lineas) > 1) {
            unset($this->lineas[$indice]);
            $this->lineas = array_values($this->lineas);
        }
    }

    public function registrarVenta(): void
    {
        $this->autorizarVentas();
        $datos = $this->validate([
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'min:1'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'lineas.*.lote_id' => ['nullable', 'integer'],
            'lineas.*.unidad_inventario_id' => ['nullable', 'integer'],
        ]);
        $tenantId = (int) auth()->user()->tenant_id;

        $venta = DB::transaction(function () use ($datos, $tenantId): Venta {
            $venta = Venta::query()->create([
                'tenant_id' => $tenantId,
                'establecimiento_id' => $this->establecimiento,
                'almacen_id' => $this->almacen,
                'usuario_id' => (int) auth()->id(),
                'folio' => 'V-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'estado' => 'borrador',
                'subtotal' => 0,
                'impuestos' => 0,
                'descuento' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($datos['lineas'] as $linea) {
                $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->findOrFail($linea['producto_id']);
                $cantidad = (float) $linea['cantidad'];
                $unidadId = filled($linea['unidad_inventario_id'] ?? null) ? (int) $linea['unidad_inventario_id'] : null;

                if ($producto->requiere_serie && $unidadId === null) {
                    $this->addError('lineas', 'Selecciona la unidad serializada de cada producto que lo requiera.');
                    throw ValidationException::withMessages(['lineas' => 'Selecciona la unidad serializada de cada producto que lo requiera.']);
                }

                if ($unidadId !== null) {
                    $unidad = UnidadInventario::query()->where('tenant_id', $tenantId)->where('producto_id', $producto->getKey())->where('estado', 'disponible')->findOrFail($unidadId);
                    if ((int) $unidad->ubicacion?->almacen_id !== $this->almacen || $cantidad !== 1.0) {
                        $this->addError('lineas', 'La unidad debe estar disponible en este almacén y su cantidad debe ser uno.');
                        throw ValidationException::withMessages(['lineas' => 'La unidad debe estar disponible en este almacén y su cantidad debe ser uno.']);
                    }
                }

                $precio = (float) $producto->precio_venta;
                $importe = round($precio * $cantidad, 2);
                DetalleVenta::query()->create([
                    'tenant_id' => $tenantId,
                    'venta_id' => $venta->getKey(),
                    'producto_id' => $producto->getKey(),
                    'unidad_inventario_id' => $unidadId,
                    'lote_id' => filled($linea['lote_id'] ?? null) ? (int) $linea['lote_id'] : null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'total' => $importe,
                ]);
                $subtotal += $importe;
            }

            $venta->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            return $venta;
        });

        $this->reset('lineas');
        $this->lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];
        Flux::toast(variant: 'success', text: 'Venta en borrador creada. Confirma para descontar existencias.');
    }

    public function confirmar(int $ventaId, VentaService $service): void
    {
        $this->autorizarVentas();
        $venta = Venta::query()->where('tenant_id', auth()->user()->tenant_id)->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)->findOrFail($ventaId);
        $service->confirmar($venta);
        Flux::toast(variant: 'success', text: 'Venta confirmada y existencias actualizadas.');
    }

    public function registrarPago(PagoVentaService $service): void
    {
        $this->autorizarVentas();
        $datos = $this->validate([
            'ventaPagoId' => ['required', 'integer', 'min:1'],
            'montoPago' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'metodoPago' => ['required', 'in:efectivo,tarjeta,transferencia,otro'],
            'referenciaPago' => ['nullable', 'string', 'max:255'],
        ]);
        Venta::query()->where('tenant_id', auth()->user()->tenant_id)
            ->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)
            ->findOrFail($datos['ventaPagoId']);
        $service->registrar($datos['ventaPagoId'], $datos['montoPago'], $datos['metodoPago'], $datos['referenciaPago'] ?: null);
        $this->reset('ventaPagoId', 'montoPago', 'referenciaPago');
        Flux::toast(variant: 'success', text: 'Pago registrado.');
    }

    private function autorizarVentas(): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $hasRole = $user->roles()
            ->whereIn('roles.slug', ['admin', 'ventas'])
            ->wherePivot('tenant_id', $tenantId)
            ->exists();
        $warehouse = Almacen::query()->where('tenant_id', $tenantId)->where('establecimiento_id', $this->establecimiento)
            ->where('activo', true)->whereHas('establecimiento', fn ($query) => $query->where('activo', true))->find($this->almacen);
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())
            ->where('establecimiento_id', $this->establecimiento)->exists();

        abort_unless($hasRole && $warehouse !== null && ($admin || $assigned), 403);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $ventas = Venta::query()->where('tenant_id', $tenantId)
            ->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)
            ->with(['detallesVenta.producto', 'pagos', 'usuario:id,name'])->latest('id')->paginate(20);
        $productos = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->orderBy('nombre')->get(['id', 'sku', 'nombre', 'unidad_medida', 'precio_venta', 'requiere_serie']);
        $existenciasPorProducto = Existencia::query()
            ->where('tenant_id', $tenantId)
            ->where('almacen_id', $this->almacen)
            ->selectRaw('producto_id, SUM(cantidad) as cantidad_disponible')
            ->groupBy('producto_id')
            ->pluck('cantidad_disponible', 'producto_id');

        return view('livewire.ventas.index', compact('ventas', 'productos', 'existenciasPorProducto'))->layout('layouts.app');
    }
}

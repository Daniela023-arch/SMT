<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\UnidadInventario;
use App\Models\Venta;
use App\Services\PagoVentaService;
use App\Services\VentaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VentaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Venta::query()
                ->where('tenant_id', $request->attributes->get('tenant_id'))
                ->where('establecimiento_id', $request->route('establecimiento'))
                ->where('almacen_id', $request->route('almacen'))
                ->withCount('detallesVenta')
                ->latest('id')
                ->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer'],
            'detalles.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'detalles.*.lote_id' => ['nullable', 'integer'],
            'detalles.*.unidad_inventario_id' => ['nullable', 'integer'],
        ]);

        $tenantId = (int) $request->attributes->get('tenant_id');
        $establecimientoId = (int) $request->route('establecimiento');
        $almacenId = (int) $request->route('almacen');
        $venta = DB::transaction(function () use ($data, $tenantId, $establecimientoId, $almacenId, $request): Venta {
            $folio = 'V-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            $venta = Venta::query()->create([
                'tenant_id' => $tenantId,
                'establecimiento_id' => $establecimientoId,
                'almacen_id' => $almacenId,
                'usuario_id' => $request->user()->getKey(),
                'folio' => $folio,
                'estado' => 'borrador',
                'subtotal' => 0,
                'impuestos' => 0,
                'descuento' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($data['detalles'] as $linea) {
                $producto = Producto::query()
                    ->where('tenant_id', $tenantId)
                    ->where('activo', true)
                    ->findOrFail($linea['producto_id']);

                $cantidad = (float) $linea['cantidad'];
                $unidadId = $linea['unidad_inventario_id'] ?? null;
                if ($producto->requiere_serie && $unidadId === null) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Este producto requiere seleccionar su número de serie.');
                }

                if ($unidadId !== null) {
                    $unidad = UnidadInventario::query()
                        ->where('tenant_id', $tenantId)
                        ->where('producto_id', $producto->getKey())
                        ->where('estado', 'disponible')
                        ->findOrFail($unidadId);
                    if ((int) $unidad->ubicacion?->almacen_id !== $almacenId || $cantidad !== 1.0) {
                        abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'La unidad serializada debe estar disponible en el almacén de la venta y su cantidad debe ser uno.');
                    }
                }

                $precio = (float) $producto->precio_venta;
                $importe = round($precio * $cantidad, 2);
                DetalleVenta::query()->create([
                    'tenant_id' => $tenantId,
                    'venta_id' => $venta->getKey(),
                    'producto_id' => $producto->getKey(),
                    'unidad_inventario_id' => $unidadId,
                    'lote_id' => $linea['lote_id'] ?? null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'total' => $importe,
                ]);
                $subtotal += $importe;
            }

            $venta->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            return $venta->load(['detallesVenta.producto', 'almacen', 'establecimiento']);
        });

        return response()->json(['data' => $venta], 201);
    }

    public function show(Request $request): JsonResponse
    {
        $venta = $this->ventaDelTenant($request);
        $this->validarAccesoEstablecimiento($request, (int) $venta->establecimiento_id);

        return response()->json(['data' => $venta->load(['detallesVenta.producto', 'pagos', 'almacen', 'establecimiento'])]);
    }

    public function confirmar(Request $request, VentaService $service): JsonResponse
    {
        $venta = $this->ventaDelTenant($request);
        $this->validarAccesoEstablecimiento($request, (int) $venta->establecimiento_id);

        return response()->json(['data' => $service->confirmar($venta)->load(['detallesVenta.producto', 'almacen', 'establecimiento'])]);
    }

    public function registrarPago(Request $request, PagoVentaService $service): JsonResponse
    {
        $data = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'metodo' => ['required', 'in:efectivo,tarjeta,transferencia,otro'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ]);
        $venta = $this->ventaDelTenant($request);
        $this->validarAccesoEstablecimiento($request, (int) $venta->establecimiento_id);
        $pago = $service->registrar((int) $venta->getKey(), (string) $data['monto'], $data['metodo'], $data['referencia'] ?? null);

        return response()->json(['data' => $pago], 201);
    }

    private function ventaDelTenant(Request $request): Venta
    {
        return Venta::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->findOrFail($request->route('venta'));
    }

    private function validarAccesoEstablecimiento(Request $request, int $establecimientoId): void
    {
        $user = $request->user();
        $tenantId = (int) $request->attributes->get('tenant_id');
        $isAdmin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')
            ->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())
            ->where('establecimiento_id', $establecimientoId)
            ->exists();

        abort_unless($isAdmin || $assigned, Response::HTTP_NOT_FOUND);
    }
}

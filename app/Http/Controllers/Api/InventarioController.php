<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Existencia;
use App\Models\StockMovimiento;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function existencias(Request $request): JsonResponse
    {
        $almacenId = (int) $request->route('almacen');
        $query = Existencia::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->where('almacen_id', $almacenId)
            ->with(['producto:id,sku,nombre,unidad_medida,precio_venta,stock_minimo', 'ubicacion:id,nombre,codigo', 'lote:id,numero_lote']);

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->integer('producto_id'));
        }

        return response()->json(['data' => $query->orderBy('producto_id')->paginate(100)]);
    }

    public function movimientos(Request $request): JsonResponse
    {
        $almacenId = (int) $request->route('almacen');
        $query = StockMovimiento::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->where(fn ($movements) => $movements->where('almacen_origen_id', $almacenId)
                ->orWhere('almacen_destino_id', $almacenId))
            ->with(['producto:id,sku,nombre', 'usuario:id,name', 'ubicacion_origen:id,nombre,codigo', 'ubicacion_destino:id,nombre,codigo'])
            ->latest('id');

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function entrada(Request $request, InventarioService $service): JsonResponse
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'integer'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'ubicacion_id' => ['nullable', 'integer'],
            'lote_id' => ['nullable', 'integer'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ]);

        $movimiento = $service->registrarEntradaEnAlmacen(
            (int) $request->attributes->get('tenant_id'),
            (int) $datos['producto_id'],
            (int) $request->route('almacen'),
            (float) $datos['cantidad'],
            isset($datos['ubicacion_id']) ? (int) $datos['ubicacion_id'] : null,
            isset($datos['lote_id']) ? (int) $datos['lote_id'] : null,
            (int) $request->user()->getKey(),
            $datos['motivo'] ?? null,
            tipo: 'entrada_manual',
        );

        return response()->json(['data' => $movimiento->load(['producto', 'ubicacion_destino'])], 201);
    }

    public function salida(Request $request, InventarioService $service): JsonResponse
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'integer'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'ubicacion_id' => ['nullable', 'integer'],
            'lote_id' => ['nullable', 'integer'],
            'motivo' => ['required', 'string', 'max:1000'],
        ]);

        $movimientos = $service->registrarSalidaDeAlmacen(
            (int) $request->attributes->get('tenant_id'),
            (int) $datos['producto_id'],
            (int) $request->route('almacen'),
            (float) $datos['cantidad'],
            isset($datos['ubicacion_id']) ? (int) $datos['ubicacion_id'] : null,
            isset($datos['lote_id']) ? (int) $datos['lote_id'] : null,
            (int) $request->user()->getKey(),
            $datos['motivo'],
            tipo: 'salida_manual',
        );

        return response()->json(['data' => $movimientos], 201);
    }
}

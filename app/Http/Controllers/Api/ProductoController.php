<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Producto::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->where('activo', true)
            ->with('categoria:id,nombre');

        if ($request->filled('buscar')) {
            $term = trim((string) $request->query('buscar'));
            $query->where(fn ($items) => $items->where('nombre', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhereHas('codigosIdentificacion', fn ($codes) => $codes->where('codigo', $term)));
        }

        return response()->json(['data' => $query->orderBy('nombre')->paginate(50)]);
    }

    public function store(Request $request, ProductoService $service): JsonResponse
    {
        $datos = $request->validate([
            'categoria_id' => ['nullable', 'integer'],
            'sku' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['sometimes', 'numeric', 'min:0'],
            'requiere_serie' => ['sometimes', 'boolean'],
        ]);

        $producto = $service->crear((int) $request->attributes->get('tenant_id'), $datos);

        return response()->json(['data' => $producto], 201);
    }
}

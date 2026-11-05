<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\Ubicacion;
use App\Services\EstablecimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstablecimientoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->attributes->get('tenant_id');
        $query = Establecimiento::query()
            ->where('tenant_id', $tenantId)
            ->with(['almacenes.ubicaciones'])
            ->orderBy('nombre');

        if (! $request->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists()) {
            $query->whereHas('usuarios', fn ($users) => $users->where('users.id', $request->user()->getKey()));
        }

        return response()->json(['data' => $query->paginate(25)]);
    }

    public function store(Request $request, EstablecimientoService $service): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('establecimientos', 'codigo')->where('tenant_id', $request->attributes->get('tenant_id'))],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:sucursal,centro_distribucion'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ]);

        $establecimiento = $service->crear((int) $request->attributes->get('tenant_id'), $datos);

        return response()->json(['data' => $establecimiento], 201);
    }

    public function storeAlmacen(Request $request, EstablecimientoService $service): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('almacenes', 'codigo')->where('establecimiento_id', $request->route('establecimiento'))],
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $almacen = $service->crearAlmacen(
            (int) $request->attributes->get('tenant_id'),
            (int) $request->route('establecimiento'),
            $datos['codigo'],
            $datos['nombre'],
        )->load('ubicaciones');

        return response()->json(['data' => $almacen], 201);
    }

    public function storeUbicacion(Request $request): JsonResponse
    {
        $tenantId = (int) $request->attributes->get('tenant_id');
        $almacenId = (int) $request->route('almacen');
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255', Rule::unique('ubicaciones', 'codigo')->where('tenant_id', $tenantId)],
            'ubicacion_padre_id' => ['nullable', 'integer'],
        ]);

        if (isset($datos['ubicacion_padre_id'])) {
            abort_unless(Ubicacion::query()
                ->where('tenant_id', $tenantId)
                ->where('almacen_id', $almacenId)
                ->whereKey($datos['ubicacion_padre_id'])
                ->exists(), 422, 'La ubicación padre debe pertenecer al mismo almacén.');
        }

        $ubicacion = Ubicacion::query()->create([
            'tenant_id' => $tenantId,
            'almacen_id' => $almacenId,
            'nombre' => $datos['nombre'],
            'codigo' => $datos['codigo'] ?? null,
            'ubicacion_padre_id' => $datos['ubicacion_padre_id'] ?? null,
        ]);

        return response()->json(['data' => $ubicacion], 201);
    }

    public function asignarUsuario(Request $request, EstablecimientoService $service): JsonResponse
    {
        $datos = $request->validate(['usuario_id' => ['required', 'integer', 'exists:users,id']]);
        $service->asignarUsuario(
            (int) $request->attributes->get('tenant_id'),
            (int) $datos['usuario_id'],
            (int) $request->route('establecimiento'),
            (int) $request->user()->getKey(),
        );

        return response()->json(['message' => 'Usuario asignado al establecimiento.']);
    }

    public function usuarios(Request $request): JsonResponse
    {
        $establecimiento = Establecimiento::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->with(['usuarios' => fn ($users) => $users->select('users.id', 'users.name', 'users.email', 'users.activo')])
            ->findOrFail($request->route('establecimiento'));

        return response()->json(['data' => $establecimiento->usuarios]);
    }
}

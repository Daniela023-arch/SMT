<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transferencia;
use App\Services\TransferenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TransferenciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->attributes->get('tenant_id');
        $user = $request->user();
        $isAdmin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $query = Transferencia::query()
            ->where('tenant_id', $tenantId)
            ->with(['almacenOrigen:id,codigo,nombre', 'almacenDestino:id,codigo,nombre', 'solicitante:id,name', 'detalles.producto:id,sku,nombre'])
            ->latest('id');

        if (! $isAdmin) {
            $assignedEstablishmentIds = DB::table('usuario_establecimiento')
                ->where('tenant_id', $tenantId)
                ->where('usuario_id', $user->getKey())
                ->select('establecimiento_id');
            $query->where(fn ($transfers) => $transfers
                ->whereIn('establecimiento_origen_id', $assignedEstablishmentIds)
                ->orWhereIn('establecimiento_destino_id', $assignedEstablishmentIds));
        }

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function store(Request $request, TransferenciaService $service): JsonResponse
    {
        $data = $request->validate([
            'almacen_origen_id' => ['required', 'integer'],
            'almacen_destino_id' => ['required', 'integer', 'different:almacen_origen_id'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer'],
            'detalles.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'detalles.*.lote_id' => ['nullable', 'integer'],
            'detalles.*.ubicacion_origen_id' => ['nullable', 'integer'],
            'detalles.*.ubicacion_destino_id' => ['nullable', 'integer'],
        ]);
        $detalles = array_map(fn ($line) => [
            'producto_id' => (int) $line['producto_id'],
            'cantidad' => (float) $line['cantidad'],
            'lote_id' => isset($line['lote_id']) ? (int) $line['lote_id'] : null,
            'ubicacion_origen_id' => isset($line['ubicacion_origen_id']) ? (int) $line['ubicacion_origen_id'] : null,
            'ubicacion_destino_id' => isset($line['ubicacion_destino_id']) ? (int) $line['ubicacion_destino_id'] : null,
        ], $data['detalles']);

        $transferencia = $service->solicitar(
            (int) $request->attributes->get('tenant_id'),
            (int) $request->user()->getKey(),
            (int) $data['almacen_origen_id'],
            (int) $data['almacen_destino_id'],
            $detalles,
            $data['motivo'] ?? null,
        );

        return response()->json(['data' => $transferencia], 201);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->transferenciaDelTenant($request)->load([
            'almacenOrigen', 'almacenDestino', 'solicitante:id,name', 'autorizador:id,name', 'detalles.producto',
        ])]);
    }

    public function autorizar(Request $request, TransferenciaService $service): JsonResponse
    {
        return response()->json(['data' => $service->autorizar(
            (int) $this->transferenciaDelTenant($request)->getKey(), (int) $request->user()->getKey(),
        )]);
    }

    public function rechazar(Request $request, TransferenciaService $service): JsonResponse
    {
        $data = $request->validate(['motivo' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $service->rechazar(
            (int) $this->transferenciaDelTenant($request)->getKey(), (int) $request->user()->getKey(), $data['motivo'],
        )]);
    }

    public function despachar(Request $request, TransferenciaService $service): JsonResponse
    {
        return response()->json(['data' => $service->despachar(
            (int) $this->transferenciaDelTenant($request)->getKey(), (int) $request->user()->getKey(),
        )]);
    }

    public function recibir(Request $request, TransferenciaService $service): JsonResponse
    {
        $data = $request->validate([
            'cantidades' => ['required', 'array', 'min:1'],
            'cantidades.*' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
        ]);

        return response()->json(['data' => $service->recibir(
            (int) $this->transferenciaDelTenant($request)->getKey(), (int) $request->user()->getKey(), $data['cantidades'],
        )]);
    }

    public function cancelar(Request $request, TransferenciaService $service): JsonResponse
    {
        return response()->json(['data' => $service->cancelar(
            (int) $this->transferenciaDelTenant($request)->getKey(), (int) $request->user()->getKey(),
        )]);
    }

    private function transferenciaDelTenant(Request $request): Transferencia
    {
        $transferencia = Transferencia::query()
            ->where('tenant_id', $request->attributes->get('tenant_id'))
            ->findOrFail($request->route('transferencia'));
        $user = $request->user();
        $tenantId = (int) $request->attributes->get('tenant_id');
        $isAdmin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')
            ->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())
            ->whereIn('establecimiento_id', [$transferencia->establecimiento_origen_id, $transferencia->establecimiento_destino_id])
            ->exists();

        abort_unless($isAdmin || $assigned, Response::HTTP_NOT_FOUND);

        return $transferencia;
    }
}

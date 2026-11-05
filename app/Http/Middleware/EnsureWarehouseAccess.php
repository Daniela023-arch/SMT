<?php

namespace App\Http\Middleware;

use App\Models\Almacen;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseAccess
{
    public function handle(Request $request, Closure $next, string $parameter = 'almacen'): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $routeValue = $request->route($parameter);
        $warehouseId = is_object($routeValue) ? $routeValue->getKey() : $routeValue;

        $warehouse = $tenantId === null || $warehouseId === null
            ? null
            : Almacen::query()
                ->where('tenant_id', $tenantId)
                ->where('activo', true)
                ->whereHas('establecimiento', fn ($query) => $query->where('activo', true))
                ->whereKey($warehouseId)
                ->first();

        if ($warehouse === null) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $routeEstablishment = $request->route('establecimiento');
        $routeEstablishmentId = is_object($routeEstablishment) ? $routeEstablishment->getKey() : $routeEstablishment;

        if ($routeEstablishmentId !== null && (int) $routeEstablishmentId !== (int) $warehouse->establecimiento_id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $userId = $request->user()?->getKey();
        $isAdmin = DB::table('usuario_rol as ur')
            ->join('roles as r', 'r.id', '=', 'ur.rol_id')
            ->where('ur.usuario_id', $userId)
            ->where('ur.tenant_id', $tenantId)
            ->where('r.tenant_id', $tenantId)
            ->where('r.slug', 'admin')
            ->exists();

        $isAssigned = DB::table('usuario_establecimiento')
            ->where('tenant_id', $tenantId)
            ->where('usuario_id', $userId)
            ->where('establecimiento_id', $warehouse->establecimiento_id)
            ->exists();

        if (! $isAdmin && ! $isAssigned) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $request->attributes->set('almacen_id', $warehouse->getKey());
        $request->attributes->set('establecimiento_id', $warehouse->establecimiento_id);

        return $next($request);
    }
}

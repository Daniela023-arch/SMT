<?php

namespace App\Http\Middleware;

use App\Models\Establecimiento;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureEstablishmentAccess
{
    public function handle(Request $request, Closure $next, string $parameter = 'establecimiento'): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $routeValue = $request->route($parameter);
        $establishmentId = is_object($routeValue) ? $routeValue->getKey() : $routeValue;

        $establishment = $tenantId === null || $establishmentId === null
            ? null
            : Establecimiento::query()
                ->where('tenant_id', $tenantId)
                ->where('activo', true)
                ->whereKey($establishmentId)
                ->first();

        if ($establishment === null) {
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
            ->where('establecimiento_id', $establishment->getKey())
            ->exists();

        if (! $isAdmin && ! $isAssigned) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $request->attributes->set('establecimiento_id', $establishment->getKey());

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\ModuloTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        $enabled = $tenantId !== null && ModuloTenant::query()
            ->where('tenant_id', $tenantId)
            ->where('habilitado', true)
            ->whereHas('modulo', fn ($query) => $query->where('clave', $module)->where('activo', true))
            ->exists();

        if (! $enabled) {
            abort(Response::HTTP_FORBIDDEN, 'Este módulo no está habilitado para el tenant.');
        }

        return $next($request);
    }
}

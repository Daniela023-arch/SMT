<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $tenant = $tenantId === null ? null : Tenant::query()->find($tenantId);

        if ($tenant === null || $tenant->estado !== 'activo') {
            abort(Response::HTTP_FORBIDDEN, 'El tenant no está activo.');
        }

        return $next($request);
    }
}

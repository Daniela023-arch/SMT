<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryType
{
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $tenant = $tenantId === null ? null : Tenant::query()->with('tipoInventario')->find($tenantId);
        $typeName = $tenant?->tipoInventario?->nombre;

        $matches = $typeName !== null && collect($types)
            ->contains(fn (string $type) => Str::slug($type) === Str::slug($typeName));

        if (! $matches) {
            abort(Response::HTTP_FORBIDDEN, 'El tipo de inventario del tenant no permite acceder a esta función.');
        }

        return $next($request);
    }
}

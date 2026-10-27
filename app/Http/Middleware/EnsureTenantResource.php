<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Prevents a route-bound tenant record from being accessed across tenants. */
class EnsureTenantResource
{
    public function handle(Request $request, Closure $next, string $parameter): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $resource = $request->route($parameter);

        if ($tenantId === null || ! $resource instanceof Model || ! array_key_exists('tenant_id', $resource->getAttributes())) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if ((string) $resource->getAttribute('tenant_id') !== (string) $tenantId) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}

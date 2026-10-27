<?php

namespace App\Http\Middleware;

use App\Models\Tenant as TenantModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->tenant_id === null) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes un tenant asignado.');
        }

        $tenant = $request->route('tenant');
        $tenantId = is_object($tenant) ? $tenant->getKey() : $tenant;

        if (is_string($tenantId) && ! ctype_digit($tenantId)) {
            $tenantId = TenantModel::query()->where('slug', $tenantId)->value('id');
        }

        if ($tenantId !== null && (string) $tenantId !== (string) $user->tenant_id) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes acceso a este tenant.');
        }

        $request->attributes->set('tenant_id', $user->tenant_id);

        return $next($request);
    }
}

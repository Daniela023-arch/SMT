<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || $user->tenant_id === null || $this->esSuperadmin($user)) {
            return $next($request);
        }

        $tenant = Tenant::query()->find($user->tenant_id);
        if ($tenant === null) {
            abort(Response::HTTP_FORBIDDEN, 'El tenant de tu cuenta ya no está disponible.');
        }

        if ($tenant->estado !== 'activo' && ! $request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }

    private function esSuperadmin($user): bool
    {
        return $user->roles()
            ->where('roles.slug', 'superadmin')
            ->whereNull('roles.tenant_id')
            ->wherePivot('tenant_id', null)
            ->exists();
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Early quota check for create endpoints. Enforce the same limit again in the write service. */
class EnsurePlanLimit
{
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;
        $limitColumn = match ($resource) {
            'usuarios' => 'limite_usuarios',
            'productos' => 'limite_productos',
            default => null,
        };

        if ($tenantId === null || $limitColumn === null) {
            abort(Response::HTTP_FORBIDDEN, 'El recurso no tiene un límite de plan configurado.');
        }

        $subscription = SuscripcionTenant::query()
            ->with('plan')
            ->where('tenant_id', $tenantId)
            ->whereIn('estado', ['activa', 'activo', 'prueba'])
            ->where('inicio_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('fin_at')->orWhere('fin_at', '>=', now()))
            ->whereHas('plan', fn ($query) => $query->where('activo', true))
            ->latest('inicio_at')
            ->first();

        if ($subscription === null) {
            abort(Response::HTTP_PAYMENT_REQUIRED, 'El tenant no tiene una suscripción vigente.');
        }

        $limit = $subscription->plan?->{$limitColumn};

        if ($limit !== null) {
            $tenant = Tenant::query()->findOrFail($tenantId);
            $currentCount = $resource === 'usuarios'
                ? $tenant->usuarios()->count()
                : $tenant->productos()->count();
            $requestedCount = filter_var($request->input('cantidad', 1), FILTER_VALIDATE_INT);

            if ($requestedCount === false || $requestedCount < 1 || $currentCount + $requestedCount > $limit) {
                abort(Response::HTTP_FORBIDDEN, 'La operación excede el límite de tu plan.');
            }
        }

        return $next($request);
    }
}

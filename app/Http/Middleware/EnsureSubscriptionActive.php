<?php

namespace App\Http\Middleware;

use App\Models\SuscripcionTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->user()?->tenant_id;

        $hasSubscription = $tenantId !== null && SuscripcionTenant::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('estado', ['activa', 'activo', 'prueba'])
            ->where('inicio_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('fin_at')->orWhere('fin_at', '>=', now()))
            ->whereHas('plan', fn ($query) => $query->where('activo', true))
            ->exists();

        if (! $hasSubscription) {
            abort(Response::HTTP_PAYMENT_REQUIRED, 'El tenant no tiene una suscripción vigente.');
        }

        return $next($request);
    }
}

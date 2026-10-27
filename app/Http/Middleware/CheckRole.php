<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || $roles === []) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para realizar esta acción.');
        }

        $hasRole = $user->newQuery()
            ->whereKey($user->getKey())
            ->whereHas('roles', function ($query) use ($roles, $user): void {
                $query->whereIn('roles.slug', $roles)
                    ->where(function ($query) use ($user): void {
                        $query->whereNull('roles.tenant_id')
                            ->orWhere('roles.tenant_id', $user->tenant_id);
                    })
                    ->where(function ($query) use ($user): void {
                        $query->whereNull('usuario_rol.tenant_id')
                            ->orWhere('usuario_rol.tenant_id', $user->tenant_id);
                    });
            })
            ->exists();

        if (! $hasRole) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes el rol requerido.');
        }

        return $next($request);
    }
}

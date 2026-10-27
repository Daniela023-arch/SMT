<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null || $permissions === []) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para realizar esta acción.');
        }

        $hasPermission = $user->newQuery()
            ->whereKey($user->getKey())
            ->whereHas('roles', function ($query) use ($permissions, $user): void {
                $query->where(function ($query) use ($user): void {
                    $query->whereNull('roles.tenant_id')
                        ->orWhere('roles.tenant_id', $user->tenant_id);
                })
                    ->where(function ($query) use ($user): void {
                        $query->whereNull('usuario_rol.tenant_id')
                            ->orWhere('usuario_rol.tenant_id', $user->tenant_id);
                    })
                    ->whereHas('permisos', fn ($query) => $query->whereIn('permisos.clave', $permissions));
            })
            ->exists();

        if (! $hasPermission) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes el permiso requerido.');
        }

        return $next($request);
    }
}

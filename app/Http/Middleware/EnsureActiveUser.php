<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isActive = $user !== null
            ? $user->newQuery()->whereKey($user->getKey())->value('activo')
            : true;

        if ($user !== null && ! $isActive) {
            abort(Response::HTTP_FORBIDDEN, 'Tu cuenta está desactivada.');
        }

        return $next($request);
    }
}

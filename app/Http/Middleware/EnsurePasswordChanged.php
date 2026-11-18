<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->debe_cambiar_password) {
            if ($request->routeIs('onboarding.show')) {
                return $next($request);
            }

            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }
}

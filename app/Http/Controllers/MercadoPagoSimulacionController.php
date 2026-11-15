<?php

namespace App\Http\Controllers;

use App\Models\SuscripcionTenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MercadoPagoSimulacionController
{
    public function __invoke(Request $request)
    {
        abort_unless(app()->isLocal(), Response::HTTP_NOT_FOUND);

        $tenantId = $request->user()?->tenant_id;
        abort_if($tenantId === null, Response::HTTP_FORBIDDEN);
        $subscription = SuscripcionTenant::query()->with(['tenant.configuracionTenant', 'plan'])
            ->where('tenant_id', $tenantId)->latest('id')->firstOrFail();

        return view('mercadopago.simulacion', [
            'tenant' => $subscription->tenant,
            'plan' => $subscription->plan,
            'admin' => $request->user(),
        ]);
    }
}

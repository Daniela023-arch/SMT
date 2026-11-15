<?php

namespace App\Http\Controllers;

use App\Models\SuscripcionTenant;
use App\Services\MercadoPagoSuscripcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MercadoPagoWebhookController
{
    public function __invoke(Request $request, MercadoPagoSuscripcionService $service): Response
    {
        $dataId = (string) $request->query('data.id', $request->input('data.id', ''));
        if (! $this->firmaValida($request, $dataId)) {
            return response('Firma no válida', Response::HTTP_UNAUTHORIZED);
        }

        $topic = (string) ($request->input('type') ?: $request->query('type', $request->query('topic', '')));
        try {
            if ($topic === 'subscription_authorized_payment' && $dataId !== '') {
                $service->procesarPagoAutorizado($dataId);
            } elseif ($topic === 'subscription_preapproval' && $dataId !== '') {
                $this->actualizarEstadoPreapproval($dataId);
            }
        } catch (Throwable $exception) {
            report($exception);
            return response('No se pudo procesar el evento', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->noContent();
    }

    private function firmaValida(Request $request, string $dataId): bool
    {
        $secret = (string) config('services.mercadopago.webhook_secret');
        $signature = (string) $request->header('x-signature', '');
        $requestId = (string) $request->header('x-request-id', '');
        if ($secret === '' || $signature === '' || $requestId === '' || $dataId === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, '');
            $parts[$key] = $value;
        }
        if (empty($parts['ts']) || empty($parts['v1'])) {
            return false;
        }

        // Formato de firma HMAC documentado por Mercado Pago: resource, request y timestamp.
        $manifest = 'id:'.mb_strtolower($dataId).';request-id:'.$requestId.';ts:'.$parts['ts'].';';
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $parts['v1']);
    }

    private function actualizarEstadoPreapproval(string $id): void
    {
        $token = (string) config('services.mercadopago.access_token');
        if ($token === '') {
            throw new \RuntimeException('Mercado Pago no tiene access token configurado.');
        }
        $response = Http::withToken($token)->acceptJson()->get('https://api.mercadopago.com/preapproval/'.rawurlencode($id));
        if (! $response->successful()) {
            throw new \RuntimeException('No se pudo verificar la suscripción en Mercado Pago.');
        }

        $status = (string) $response->json('status', 'pending');
        SuscripcionTenant::query()
            ->where('proveedor', 'mercadopago')
            ->where('suscripcion_externa_id', $id)
            ->whereNotIn('estado', ['activa', 'cancelada'])
            ->update(['estado' => match ($status) {
                'authorized' => 'pago_autorizado',
                'paused' => 'pausada',
                'cancelled', 'canceled' => 'cancelada',
                default => 'pago_pendiente',
            }]);
    }
}

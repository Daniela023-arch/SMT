<?php

namespace App\Services;

use App\Models\PagoSuscripcion;
use App\Models\SuscripcionTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MercadoPagoSuscripcionService
{
    public function iniciar(SuscripcionTenant $suscripcion): string
    {
        $suscripcion->loadMissing(['tenant', 'tenant.usuarios', 'plan']);
        $token = config('services.mercadopago.access_token');
        if (! is_string($token) || $token === '') {
            throw ValidationException::withMessages(['pago' => 'Falta configurar MERCADOPAGO_ACCESS_TOKEN en el archivo .env.']);
        }

        if ((float) $suscripcion->plan->precio <= 0) {
            throw ValidationException::withMessages(['pago' => 'El plan seleccionado no tiene un costo mayor a cero. Configura el precio del plan antes de cobrar.']);
        }

        [$frequency, $frequencyType] = $this->frecuencia($suscripcion->plan->periodicidad);
        if ($suscripcion->suscripcion_externa_id) {
            $existing = $this->sincronizarSuscripcion($suscripcion->suscripcion_externa_id);
            if (! in_array($existing['status'] ?? '', ['cancelled', 'canceled', 'rejected'], true) && ! empty($existing['init_point'])) {
                return (string) $existing['init_point'];
            }
            if (! in_array($existing['status'] ?? '', ['cancelled', 'canceled', 'rejected'], true)) {
                throw ValidationException::withMessages(['pago' => 'Ya existe un intento de pago. Revisa su estado antes de iniciar otro.']);
            }
        }
        $admin = $suscripcion->tenant->usuarios()->orderBy('id')->first();
        if ($admin === null) {
            throw new RuntimeException('No se encontró el administrador inicial del tenant.');
        }

        $response = Http::withToken($token)->acceptJson()->asJson()->post('https://api.mercadopago.com/preapproval', [
            'reason' => 'Suscripción '.$suscripcion->plan->nombre.' - '.$suscripcion->tenant->nombre,
            'external_reference' => 'tenant-'.$suscripcion->tenant_id.'-subscription-'.$suscripcion->getKey(),
            'payer_email' => $admin->email,
            'auto_recurring' => [
                'frequency' => $frequency,
                'frequency_type' => $frequencyType,
                'transaction_amount' => (float) $suscripcion->plan->precio,
                'currency_id' => mb_strtoupper($suscripcion->plan->moneda ?: 'MXN'),
            ],
            'back_url' => config('services.mercadopago.back_url') ?: route('onboarding.show'),
            'status' => 'pending',
        ]);

        if (! $response->successful()) {
            report(new RuntimeException('Mercado Pago rechazó la creación de la suscripción: '.$response->status().' '.$response->body()));
            throw ValidationException::withMessages(['pago' => 'Mercado Pago no pudo iniciar el cobro. Revisa la configuración y vuelve a intentarlo.']);
        }

        $data = $response->json();
        if (! is_array($data) || empty($data['id']) || empty($data['init_point'])) {
            throw new RuntimeException('Mercado Pago respondió sin identificador o enlace de pago.');
        }

        DB::transaction(function () use ($suscripcion, $data): void {
            $suscripcion->forceFill([
                'proveedor' => 'mercadopago',
                'suscripcion_externa_id' => (string) $data['id'],
                'estado' => 'pago_pendiente',
            ])->save();

            PagoSuscripcion::query()->create([
                'suscripcion_id' => $suscripcion->getKey(),
                'monto' => $suscripcion->plan->precio,
                'moneda' => mb_strtoupper($suscripcion->plan->moneda ?: 'MXN'),
                'estado' => 'pendiente',
            ]);
        });

        return (string) $data['init_point'];
    }

    public function sincronizarSuscripcion(string $id): array
    {
        $response = Http::withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()->get('https://api.mercadopago.com/preapproval/'.rawurlencode($id));

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo consultar la suscripción en Mercado Pago.');
        }

        return $response->json();
    }

    public function procesarPagoAutorizado(string $id): void
    {
        $response = Http::withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()->get('https://api.mercadopago.com/authorized_payments/'.rawurlencode($id));

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo consultar el pago autorizado en Mercado Pago.');
        }

        $pago = $response->json();
        if (! is_array($pago) || ($pago['status'] ?? null) !== 'processed') {
            return;
        }

        $mpSubscriptionId = (string) ($pago['preapproval_id'] ?? '');
        if ($mpSubscriptionId === '') {
            return;
        }

        DB::transaction(function () use ($pago, $mpSubscriptionId, $id): void {
            $subscription = SuscripcionTenant::query()
                ->with('plan')
                ->where('proveedor', 'mercadopago')
                ->where('suscripcion_externa_id', $mpSubscriptionId)
                ->lockForUpdate()
                ->first();

            if ($subscription === null) {
                return;
            }

            $existingPayment = PagoSuscripcion::query()->where('referencia_externa', $id)->lockForUpdate()->first();
            if ($existingPayment?->estado === 'pagado') {
                return;
            }

            $amount = (float) ($pago['transaction_amount'] ?? $subscription->plan->precio);
            $currency = mb_strtoupper((string) ($pago['currency_id'] ?? $subscription->plan->moneda ?? 'MXN'));
            $pendingPayment = PagoSuscripcion::query()
                ->where('suscripcion_id', $subscription->getKey())
                ->where('estado', 'pendiente')
                ->latest('id')
                ->first();
            $expectedAmount = $pendingPayment ? (float) $pendingPayment->monto : (float) $subscription->plan->precio;
            $expectedCurrency = mb_strtoupper($pendingPayment?->moneda ?: ($subscription->plan->moneda ?: 'MXN'));
            if (abs($amount - $expectedAmount) >= 0.01 || $currency !== $expectedCurrency) {
                throw new \RuntimeException('El pago confirmado no coincide con el monto y moneda del plan local.');
            }
            $paymentValues = [
                'suscripcion_id' => $subscription->getKey(),
                'monto' => $amount,
                'moneda' => $currency,
                'estado' => 'pagado',
                'pagado_at' => now(),
            ];
            if ($existingPayment !== null) {
                $existingPayment->update($paymentValues);
            } elseif ($pendingPayment !== null) {
                $pendingPayment->update(array_merge($paymentValues, ['referencia_externa' => $id]));
            } else {
                PagoSuscripcion::query()->create(array_merge($paymentValues, ['referencia_externa' => $id]));
            }

            $inicio = now();
            if ($subscription->fin_at !== null && $subscription->fin_at->isFuture()) {
                $inicio = $subscription->fin_at->copy();
            }
            $subscription->forceFill([
                'estado' => 'activa',
                'inicio_at' => $subscription->inicio_at ?? now(),
                'fin_at' => $this->fechaFin($subscription->plan->periodicidad, $inicio),
            ])->save();
            $subscription->tenant()->update(['estado' => 'activo']);
        });
    }

    private function frecuencia(string $periodicidad): array
    {
        return match (mb_strtolower($periodicidad)) {
            'mensual', 'mes' => [1, 'months'],
            'trimestral', 'trimestre' => [3, 'months'],
            'anual', 'año', 'ano' => [12, 'months'],
            default => throw ValidationException::withMessages(['plan' => 'El plan debe tener periodicidad mensual, trimestral o anual.']),
        };
    }

    private function fechaFin(string $periodicidad, \Illuminate\Support\Carbon $inicio): \Illuminate\Support\Carbon
    {
        return match (mb_strtolower($periodicidad)) {
            'mensual', 'mes' => $inicio->copy()->addMonth(),
            'trimestral', 'trimestre' => $inicio->copy()->addMonths(3),
            'anual', 'año', 'ano' => $inicio->copy()->addYear(),
            default => throw ValidationException::withMessages(['plan' => 'El plan debe tener periodicidad mensual, trimestral o anual.']),
        };
    }
}

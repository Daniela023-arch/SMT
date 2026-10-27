<?php

namespace App\Services;

use App\Models\EventoWebhook;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class WebhookService
{
    /** @param array<string, mixed> $payload */
    public function recibir(string $proveedor, string $eventoId, string $tipo, array $payload): EventoWebhook
    {
        try {
            return EventoWebhook::query()->firstOrCreate(
                ['proveedor' => $proveedor, 'evento_externo_id' => $eventoId],
                ['tipo' => $tipo, 'payload' => $payload, 'estado' => 'recibido'],
            );
        } catch (UniqueConstraintViolationException) {
            // Concurrent deliveries can race on the unique provider/event key.
            return EventoWebhook::query()->where('proveedor', $proveedor)->where('evento_externo_id', $eventoId)->firstOrFail();
        }
    }

    public function iniciarProcesamiento(EventoWebhook $evento): bool
    {
        return DB::transaction(function () use ($evento): bool {
            $evento = EventoWebhook::query()->whereKey($evento->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($evento->estado, ['procesado', 'procesando'], true)) {
                return false;
            }

            $evento->forceFill([
                'estado' => 'procesando',
                'intentos' => $evento->intentos + 1,
                'ultimo_error' => null,
            ])->save();

            return true;
        });
    }

    public function marcarProcesado(EventoWebhook $evento): EventoWebhook
    {
        $evento->forceFill([
            'estado' => 'procesado',
            'procesado_at' => now(),
            'ultimo_error' => null,
        ])->save();

        return $evento->refresh();
    }

    public function marcarFallido(EventoWebhook $evento, string $error): EventoWebhook
    {
        $evento->forceFill(['estado' => 'fallido', 'ultimo_error' => $error])->save();

        return $evento->refresh();
    }
}

<?php
namespace App\Services;

use App\Models\{AsignacionPromocion,PromocionSuscripcion,SuscripcionTenant};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromocionService
{
    public function asignar(int $suscripcionId, string $codigo): AsignacionPromocion
    {
        return DB::transaction(function () use ($suscripcionId, $codigo) {
            SuscripcionTenant::query()->findOrFail($suscripcionId);
            $promo = PromocionSuscripcion::query()->where('codigo', $codigo)->lockForUpdate()->firstOrFail();
            $ahora = now();
            if (!$promo->activo || ($promo->vigencia_inicio && $ahora->lt($promo->vigencia_inicio)) || ($promo->vigencia_fin && $ahora->gt($promo->vigencia_fin))) {
                throw ValidationException::withMessages(['promocion' => 'Promoción no vigente.']);
            }
            $existente = AsignacionPromocion::query()->where('suscripcion_id', $suscripcionId)->where('promocion_id', $promo->id)->first();
            if ($existente) return $existente;
            if ($promo->usos_maximos !== null && AsignacionPromocion::query()->where('promocion_id', $promo->id)->count() >= $promo->usos_maximos) {
                throw ValidationException::withMessages(['promocion' => 'La promoción alcanzó su límite de usos.']);
            }
            return AsignacionPromocion::query()->create(['suscripcion_id' => $suscripcionId, 'promocion_id' => $promo->id, 'aplicada_at' => $ahora]);
        });
    }
}

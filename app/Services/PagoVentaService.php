<?php
namespace App\Services;

use App\Models\{Pago,Venta};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PagoVentaService
{
    public function registrar(int $ventaId, string $monto, string $metodo, ?string $referencia = null): Pago
    {
        return DB::transaction(function () use ($ventaId, $monto, $metodo, $referencia) {
            $venta = Venta::query()->lockForUpdate()->findOrFail($ventaId);
            if ($venta->estado !== 'confirmada' || !is_numeric($monto) || (float) $monto <= 0) {
                throw ValidationException::withMessages(['pago' => 'Venta no confirmada o monto inválido.']);
            }
            $pagadoCentavos = (int) round((float) Pago::query()->where('venta_id', $ventaId)->where('estado', 'pagado')->sum('monto') * 100);
            $nuevoCentavos = (int) round((float) $monto * 100);
            if ($pagadoCentavos + $nuevoCentavos > (int) round((float) $venta->total * 100)) {
                throw ValidationException::withMessages(['monto' => 'El pago supera el saldo pendiente.']);
            }
            if ($referencia !== null && Pago::query()->where('tenant_id', $venta->tenant_id)->where('referencia', $referencia)->exists()) {
                throw ValidationException::withMessages(['referencia' => 'El pago ya fue registrado.']);
            }
            return Pago::query()->create([
                'tenant_id' => $venta->tenant_id, 'venta_id' => $ventaId, 'monto' => $monto,
                'metodo' => $metodo, 'referencia' => $referencia, 'estado' => 'pagado', 'pagado_at' => now(),
            ]);
        });
    }
}

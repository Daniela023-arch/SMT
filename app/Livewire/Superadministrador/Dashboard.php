<?php

namespace App\Livewire\Superadministrador;

use App\Models\PlanSuscripcion;
use App\Models\Auditoria;
use App\Models\PagoSuscripcion;
use App\Models\SolicitudInventario;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Superadministración | MULTIVERSE')]
class Dashboard extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->roles()
            ->where('roles.slug', 'superadmin')
            ->whereNull('roles.tenant_id')
            ->wherePivot('tenant_id', null)
            ->exists(), 403);
    }

    public function render()
    {
        $solicitudes = SolicitudInventario::query()
            ->with('tipoInventario:id,nombre')
            ->latest('id')
            ->limit(8)
            ->get();

        $tenantsRecientes = Tenant::query()
            ->latest('id')
            ->limit(6)
            ->get(['id', 'nombre', 'slug', 'estado', 'created_at']);

        $crecimiento = collect(range(5, 0))->map(function (int $mesesAtras): array {
            $mes = now()->startOfMonth()->subMonths($mesesAtras);
            return [
                'mes' => ucfirst($mes->translatedFormat('M')),
                'total' => Tenant::query()->where('created_at', '<', $mes->copy()->addMonth())->count(),
            ];
        });

        $tenantsPorTipo = Tenant::query()
            ->join('tipos_inventario', 'tipos_inventario.id', '=', 'tenants.tipo_inventario_id')
            ->selectRaw('tipos_inventario.nombre as nombre, COUNT(tenants.id) as total')
            ->groupBy('tipos_inventario.nombre')
            ->orderByDesc('total')
            ->get();

        $actividad = Auditoria::query()->with(['usuario:id,name', 'tenant:id,nombre'])->latest()->limit(5)->get();
        $ingresosPorMes = collect(range(5, 0))->map(function (int $mesesAtras): array {
            $mes = now()->startOfMonth()->subMonths($mesesAtras);
            return [
                'mes' => ucfirst($mes->translatedFormat('M')),
                'total' => (float) PagoSuscripcion::query()->where('estado', 'pagado')
                    ->whereBetween('pagado_at', [$mes, $mes->copy()->endOfMonth()])->sum('monto'),
            ];
        });
        $pagosMes = PagoSuscripcion::query()->where('estado', 'pagado')->whereBetween('pagado_at', [now()->startOfMonth(), now()->endOfMonth()]);
        $suscripcionesPorVencer = SuscripcionTenant::query()->with('tenant:id,nombre')
            ->whereIn('estado', ['activa', 'activo', 'prueba'])
            ->whereBetween('fin_at', [now(), now()->addDays(15)])
            ->orderBy('fin_at')->limit(5)->get();

        return view('livewire.superadministrador.dashboard', [
            'totalTenants' => Tenant::query()->count(),
            'tenantsActivos' => Tenant::query()->where('estado', 'activo')->count(),
            'solicitudesPendientes' => SolicitudInventario::query()->where('estado', 'pendiente')->count(),
            'suscripcionesActivas' => SuscripcionTenant::query()
                ->whereIn('estado', ['activa', 'activo', 'prueba'])
                ->where('inicio_at', '<=', now())
                ->where(fn ($query) => $query->whereNull('fin_at')->orWhere('fin_at', '>=', now()))
                ->whereHas('plan', fn ($query) => $query->where('activo', true))
                ->count(),
            'planesActivos' => PlanSuscripcion::query()->where('activo', true)->count(),
            'usuariosRegistrados' => User::query()->count(),
            'porVencer' => SuscripcionTenant::query()->whereIn('estado', ['activa', 'activo', 'prueba'])
                ->whereBetween('fin_at', [now(), now()->addDays(15)])->count(),
            'ingresosMes' => (float) (clone $pagosMes)->sum('monto'),
            'pagosRechazados' => PagoSuscripcion::query()->where('estado', 'rechazado')->count(),
            'crecimiento' => $crecimiento,
            'ingresosPorMes' => $ingresosPorMes,
            'suscripcionesPorVencer' => $suscripcionesPorVencer,
            'tenantsPorTipo' => $tenantsPorTipo,
            'actividad' => $actividad,
            'solicitudes' => $solicitudes,
            'tenantsRecientes' => $tenantsRecientes,
        ])->layout('layouts.superadministrador');
    }
}

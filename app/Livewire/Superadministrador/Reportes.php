<?php

namespace App\Livewire\Superadministrador;

use App\Models\Auditoria;
use App\Models\PagoSuscripcion;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('Reportes globales | MULTIVERSE')]
class Reportes extends Component
{
    public string $desde;
    public string $hasta;

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->format('Y-m-d');
    }

    private function fechas(): array
    {
        return $this->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);
    }

    public function exportar(): StreamedResponse
    {
        $fechas = $this->fechas();
        $tenants = Tenant::query()->with('tipoInventario:id,nombre')
            ->whereBetween('created_at', [$fechas['desde'].' 00:00:00', $fechas['hasta'].' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($tenants): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Tenant', 'Slug', 'Correo', 'Tipo', 'Estado', 'Fecha de alta']);
            foreach ($tenants as $tenant) {
                fputcsv($output, array_map([$this, 'seguroCsv'], [
                    $tenant->nombre,
                    $tenant->slug,
                    $tenant->email,
                    $tenant->tipoInventario?->nombre,
                    $tenant->estado,
                    $tenant->created_at?->toDateTimeString(),
                ]));
            }
            fclose($output);
        }, 'multiverse-tenants-'.$fechas['desde'].'-'.$fechas['hasta'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function seguroCsv(?string $valor): string
    {
        $valor ??= '';
        return preg_match('/^[=+@\-\t\r]/', $valor) ? "'".$valor : $valor;
    }

    public function render()
    {
        $desde = $this->desde.' 00:00:00';
        $hasta = $this->hasta.' 23:59:59';
        $tenantsNuevos = Tenant::query()->whereBetween('created_at', [$desde, $hasta])->count();
        $pagos = PagoSuscripcion::query()->where('estado', 'pagado')->whereBetween('pagado_at', [$desde, $hasta]);

        return view('livewire.superadministrador.reportes', [
            'tenantsNuevos' => $tenantsNuevos,
            'ingresos' => (float) (clone $pagos)->sum('monto'),
            'pagosRegistrados' => (clone $pagos)->count(),
            'accionesAdmin' => Auditoria::query()->whereBetween('created_at', [$desde, $hasta])->count(),
            'suscripcionesNuevas' => SuscripcionTenant::query()->whereBetween('inicio_at', [$desde, $hasta])->count(),
            'tenantsPorEstado' => Tenant::query()->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->orderBy('estado')->get(),
            'tenantsPorTipo' => Tenant::query()->join('tipos_inventario', 'tipos_inventario.id', '=', 'tenants.tipo_inventario_id')
                ->selectRaw('tipos_inventario.nombre as tipo, COUNT(tenants.id) as total')->groupBy('tipos_inventario.nombre')->orderBy('tipo')->get(),
            'actividad' => Auditoria::query()->with(['usuario:id,name', 'tenant:id,nombre'])->whereBetween('created_at', [$desde, $hasta])->latest('created_at')->limit(12)->get(),
        ])->layout('layouts.superadministrador');
    }
}

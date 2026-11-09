<?php

namespace App\Livewire\Superadministrador;

use App\Models\Modulo;
use App\Models\PlanSuscripcion;
use App\Models\SolicitudInventario;
use App\Services\ProvisionamientoTenantService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Solicitudes y altas | MULTIVERSE')]
class Solicitudes extends Component
{
    public string $buscar = '';

    public string $estadoFiltro = 'pendiente';

    public ?int $solicitudSeleccionada = null;

    public ?int $planId = null;

    public string $adminNombre = '';

    public array $modulos = ['sucursales', 'productos', 'stock', 'ventas', 'transferencias'];

    public string $motivoRechazo = '';

    public ?string $passwordTemporal = null;

    public ?string $credencialesEmail = null;

    public function seleccionar(int $solicitudId): void
    {
        $solicitud = SolicitudInventario::query()->with('tipoInventario')->findOrFail($solicitudId);
        $this->solicitudSeleccionada = $solicitudId;
        $this->adminNombre = $this->extraerResponsable($solicitud->observaciones) ?? '';
        $this->planId = PlanSuscripcion::query()->where('activo', true)->orderBy('precio')->value('id');
        $this->reset('passwordTemporal', 'credencialesEmail', 'motivoRechazo');
    }

    public function aprobar(ProvisionamientoTenantService $service): void
    {
        $datos = $this->validate([
            'solicitudSeleccionada' => ['required', 'integer', 'exists:solicitudes_inventario,id'],
            'planId' => ['required', 'integer', 'exists:planes_suscripcion,id'],
            'adminNombre' => ['required', 'string', 'min:2', 'max:120'],
            'modulos' => ['required', 'array', 'min:1'],
            'modulos.*' => ['required', 'string', 'distinct', 'exists:modulos,clave'],
        ]);

        $resultado = $service->aprobarSolicitud(
            (int) $datos['solicitudSeleccionada'],
            (int) $datos['planId'],
            $datos['adminNombre'],
            $datos['modulos'],
            (int) auth()->id(),
        );

        $this->passwordTemporal = $resultado['passwordTemporal'];
        $this->credencialesEmail = $resultado['administrador']->email;
        Flux::toast(variant: 'success', text: 'Tenant, suscripción y usuario administrador creados.');
    }

    public function rechazar(ProvisionamientoTenantService $service): void
    {
        $datos = $this->validate([
            'solicitudSeleccionada' => ['required', 'integer', 'exists:solicitudes_inventario,id'],
            'motivoRechazo' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $service->rechazarSolicitud((int) $datos['solicitudSeleccionada'], $datos['motivoRechazo'], (int) auth()->id());
        $this->reset('solicitudSeleccionada', 'motivoRechazo', 'adminNombre');
        Flux::toast(variant: 'success', text: 'Solicitud marcada como rechazada.');
    }

    private function extraerResponsable(?string $observaciones): ?string
    {
        if ($observaciones === null || ! preg_match('/(?:^|\n)Responsable:\s*(.+)/u', $observaciones, $coincidencia)) {
            return null;
        }

        return trim($coincidencia[1]);
    }

    public function render()
    {
        $solicitudes = SolicitudInventario::query()
            ->with('tipoInventario:id,nombre')
            ->when($this->estadoFiltro !== 'todas', fn ($query) => $query->where('estado', $this->estadoFiltro))
            ->when($this->buscar !== '', fn ($query) => $query->where(fn ($solicitudes) => $solicitudes
                ->where('nombre_negocio', 'like', '%'.$this->buscar.'%')
                ->orWhere('email_contacto', 'like', '%'.$this->buscar.'%')))
            ->latest('id')
            ->paginate(12);

        return view('livewire.superadministrador.solicitudes', [
            'solicitudes' => $solicitudes,
            'solicitudActual' => $this->solicitudSeleccionada
                ? SolicitudInventario::query()->with('tipoInventario:id,nombre')->find($this->solicitudSeleccionada)
                : null,
            'planes' => PlanSuscripcion::query()->where('activo', true)->orderBy('precio')->get(),
            'modulosDisponibles' => Modulo::query()->where('activo', true)->orderBy('nombre')->get(),
            'pendientes' => SolicitudInventario::query()->where('estado', 'pendiente')->count(),
        ])->layout('layouts.superadministrador');
    }
}

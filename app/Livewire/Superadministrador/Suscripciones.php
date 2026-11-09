<?php

namespace App\Livewire\Superadministrador;

use App\Models\PagoSuscripcion;
use App\Models\PlanSuscripcion;
use App\Models\SuscripcionTenant;
use App\Models\Tenant;
use App\Services\AuditoriaService;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Suscripciones y pagos | MULTIVERSE')]
class Suscripciones extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $estadoFiltro = 'todos';
    public int $suscripcionId = 0;
    public int $nuevoPlanId = 0;
    public string $nuevoEstado = '';
    public string $finAt = '';
    public string $montoPago = '';
    public string $referencia = '';
    public int $tenantNuevoId = 0;
    public int $planNuevoId = 0;

    public function mount(): void
    {
        $this->planNuevoId = (int) (PlanSuscripcion::query()->where('activo', true)->orderBy('precio')->value('id') ?? 0);
        $this->tenantNuevoId = (int) (Tenant::query()->where('estado', 'activo')->orderBy('nombre')->value('id') ?? 0);
    }

    public function seleccionar(int $id): void
    {
        $suscripcion = SuscripcionTenant::query()->findOrFail($id);
        $this->suscripcionId = $id;
        $this->nuevoPlanId = (int) $suscripcion->plan_id;
        $this->nuevoEstado = $suscripcion->estado;
        $this->finAt = $suscripcion->fin_at?->format('Y-m-d') ?? '';
        $this->reset('montoPago', 'referencia');
    }

    public function guardarCambios(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'suscripcionId' => ['required', 'integer', 'exists:suscripciones_tenant,id'],
            'nuevoPlanId' => ['required', 'integer', 'exists:planes_suscripcion,id'],
            'nuevoEstado' => ['required', Rule::in(['activa', 'prueba', 'suspendida', 'cancelada', 'vencida'])],
            'finAt' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $subscription = SuscripcionTenant::query()->findOrFail($datos['suscripcionId']);
        $antes = $subscription->only(['plan_id', 'estado', 'fin_at']);
        $subscription->update([
            'plan_id' => $datos['nuevoPlanId'],
            'estado' => $datos['nuevoEstado'],
            'fin_at' => $datos['finAt'] ? Carbon::parse($datos['finAt'])->endOfDay() : null,
        ]);
        $auditoria->registrar((int) auth()->id(), 'suscripcion_actualizada', 'SuscripcionTenant', $subscription->id, (int) $subscription->tenant_id, $antes, $subscription->only(['plan_id', 'estado', 'fin_at']));
        Flux::toast(variant: 'success', text: 'Suscripción actualizada.');
    }

    public function crearSuscripcion(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'tenantNuevoId' => ['required', 'integer', 'exists:tenants,id'],
            'planNuevoId' => ['required', 'integer', 'exists:planes_suscripcion,id'],
        ]);

        $tenant = Tenant::query()->where('estado', 'activo')->findOrFail($datos['tenantNuevoId']);
        $plan = PlanSuscripcion::query()->where('activo', true)->findOrFail($datos['planNuevoId']);
        $subscription = SuscripcionTenant::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'estado' => 'activa',
            'inicio_at' => now(),
            'fin_at' => $this->periodEnd($plan->periodicidad),
        ]);
        $auditoria->registrar((int) auth()->id(), 'suscripcion_creada', 'SuscripcionTenant', $subscription->id, $tenant->id, null, ['plan_id' => $plan->id]);
        Flux::toast(variant: 'success', text: 'Suscripción creada.');
    }

    public function registrarPago(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'suscripcionId' => ['required', 'integer', 'exists:suscripciones_tenant,id'],
            'montoPago' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'referencia' => ['nullable', 'string', 'max:255', Rule::unique('pagos_suscripcion', 'referencia_externa')],
        ]);

        $subscription = SuscripcionTenant::query()->findOrFail($datos['suscripcionId']);
        $payment = DB::transaction(fn () => PagoSuscripcion::query()->create([
            'suscripcion_id' => $subscription->id,
            'monto' => $datos['montoPago'],
            'moneda' => $subscription->plan?->moneda ?? 'MXN',
            'estado' => 'pagado',
            'referencia_externa' => $datos['referencia'] ?: null,
            'pagado_at' => now(),
        ]));
        $auditoria->registrar((int) auth()->id(), 'pago_suscripcion_registrado', 'PagoSuscripcion', $payment->id, (int) $subscription->tenant_id, null, ['suscripcion_id' => $subscription->id, 'monto' => $payment->monto, 'referencia' => $payment->referencia_externa]);
        $this->reset('montoPago', 'referencia');
        Flux::toast(variant: 'success', text: 'Pago registrado.');
    }

    private function periodEnd(string $periodicidad): Carbon
    {
        return match ($periodicidad) {
            'mensual' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    public function render()
    {
        $suscripciones = SuscripcionTenant::query()
            ->with(['tenant:id,nombre,slug,estado', 'plan:id,nombre,precio,moneda,periodicidad', 'pagosSuscripcion'])
            ->when($this->estadoFiltro !== 'todos', fn ($query) => $query->where('estado', $this->estadoFiltro))
            ->when($this->buscar !== '', fn ($query) => $query->whereHas('tenant', fn ($tenant) => $tenant->where('nombre', 'like', '%'.$this->buscar.'%')))
            ->latest('inicio_at')
            ->paginate(12);

        return view('livewire.superadministrador.suscripciones', [
            'suscripciones' => $suscripciones,
            'planes' => PlanSuscripcion::query()->orderBy('nombre')->get(),
            'planesActivos' => PlanSuscripcion::query()->where('activo', true)->orderBy('nombre')->get(),
            'tenantsActivos' => Tenant::query()->where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']),
            'suscripcionActual' => $this->suscripcionId ? SuscripcionTenant::query()->with(['tenant', 'plan', 'pagosSuscripcion'])->find($this->suscripcionId) : null,
        ])->layout('layouts.superadministrador');
    }
}

<?php

namespace App\Livewire\Superadministrador;

use App\Models\PlanSuscripcion;
use App\Services\AuditoriaService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Planes | MULTIVERSE')]
class Planes extends Component
{
    public ?int $planEditando = null;
    public string $nombre = '';
    public string $precio = '';
    public string $periodicidad = 'mensual';
    public string $limiteUsuarios = '';
    public string $limiteProductos = '';

    public function guardar(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('planes_suscripcion', 'nombre')->ignore($this->planEditando)],
            'precio' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'periodicidad' => ['required', Rule::in(['mensual', 'trimestral', 'anual'])],
            'limiteUsuarios' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'limiteProductos' => ['nullable', 'integer', 'min:1', 'max:10000000'],
        ]);

        $attributes = [
            'nombre' => $datos['nombre'],
            'precio' => $datos['precio'],
            'moneda' => 'MXN',
            'periodicidad' => $datos['periodicidad'],
            'limite_usuarios' => $datos['limiteUsuarios'] ?: null,
            'limite_productos' => $datos['limiteProductos'] ?: null,
        ];

        if ($this->planEditando) {
            $plan = PlanSuscripcion::query()->findOrFail($this->planEditando);
            $antes = $plan->only(['nombre', 'precio', 'periodicidad', 'limite_usuarios', 'limite_productos', 'activo']);
            $plan->update($attributes);
            $auditoria->registrar((int) auth()->id(), 'plan_actualizado', 'PlanSuscripcion', $plan->id, null, $antes, $attributes);
        } else {
            $plan = PlanSuscripcion::query()->create($attributes + ['activo' => true]);
            $auditoria->registrar((int) auth()->id(), 'plan_creado', 'PlanSuscripcion', $plan->id, null, null, $attributes);
        }

        $this->cancelarEdicion();
        Flux::toast(variant: 'success', text: 'Plan guardado.');
    }

    public function editar(int $planId): void
    {
        $plan = PlanSuscripcion::query()->findOrFail($planId);
        $this->planEditando = $plan->id;
        $this->nombre = $plan->nombre;
        $this->precio = (string) $plan->precio;
        $this->periodicidad = $plan->periodicidad;
        $this->limiteUsuarios = (string) ($plan->limite_usuarios ?? '');
        $this->limiteProductos = (string) ($plan->limite_productos ?? '');
    }

    public function cambiarActivo(int $planId, AuditoriaService $auditoria): void
    {
        $plan = PlanSuscripcion::query()->findOrFail($planId);
        $antes = $plan->activo;
        $plan->update(['activo' => ! $plan->activo]);
        $auditoria->registrar((int) auth()->id(), 'plan_estado_actualizado', 'PlanSuscripcion', $plan->id, null, ['activo' => $antes], ['activo' => $plan->activo]);
        Flux::toast(variant: 'success', text: $plan->activo ? 'Plan activado.' : 'Plan desactivado. Las suscripciones existentes se conservan.');
    }

    public function cancelarEdicion(): void
    {
        $this->reset('planEditando', 'nombre', 'precio', 'limiteUsuarios', 'limiteProductos');
        $this->periodicidad = 'mensual';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.superadministrador.planes', [
            'planes' => PlanSuscripcion::query()->withCount('suscripciones')->orderBy('precio')->get(),
        ])->layout('layouts.superadministrador');
    }
}

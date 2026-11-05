<?php

namespace App\Livewire\Publico;

use App\Models\SolicitudInventario;
use App\Models\TipoInventario;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Solicitar MULTIVERSE')]
class Solicitud extends Component
{
    public string $negocio = '';
    public string $responsable = '';
    public string $email = '';
    public string $telefono = '';
    public string $plan = '';
    public string $descripcion = '';
    public string $comentarios = '';
    public bool $privacidad = false;
    public ?string $folio = null;

    public function mount(): void
    {
        $plan = request()->query('plan');
        $this->plan = in_array($plan, ['Mensual', 'Trimestral', 'Anual'], true) ? $plan : '';
    }

    public function enviar(): void
    {
        $datos = $this->validate([
            'negocio' => ['required', 'string', 'min:2', 'max:120'],
            'responsable' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'regex:/^[0-9 +()\-]{8,20}$/'],
            'plan' => ['required', 'in:Mensual,Trimestral,Anual'],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
            'comentarios' => ['nullable', 'string', 'max:1000'],
            'privacidad' => ['accepted'],
        ], [
            'telefono.regex' => 'Ingresa un teléfono válido.',
            'privacidad.accepted' => 'Debes aceptar el aviso para enviar la solicitud.',
        ]);

        $tipo = TipoInventario::firstOrCreate(
            ['nombre' => 'Ferretería'],
            ['descripcion' => 'Operación ferretera con inventario multisucursal.'],
        );

        $solicitud = SolicitudInventario::create([
            'tipo_inventario_id' => $tipo->id,
            'nombre_negocio' => $datos['negocio'],
            'email_contacto' => $datos['email'],
            'estado' => 'pendiente',
            'observaciones' => implode("\n", array_filter([
                'Responsable: '.$datos['responsable'],
                'Teléfono: '.$datos['telefono'],
                'Periodicidad solicitada: '.$datos['plan'],
                'Descripción: '.$datos['descripcion'],
                filled($datos['comentarios']) ? 'Comentarios: '.$datos['comentarios'] : null,
            ])),
        ]);

        $this->folio = 'SOL-'.now()->format('Y').'-'.str_pad((string) $solicitud->id, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.publico.solicitud')->layout('layouts.public.site');
    }
}

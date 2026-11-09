<?php

namespace App\Livewire\Publico;

use App\Models\PlanSuscripcion;
use App\Models\PromocionPublica;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('MULTIVERSE | Inventario para ferreterías')]
class Inicio extends Component
{
    public function render()
    {
        return view('livewire.publico.inicio', [
            'planes' => PlanSuscripcion::query()->where('activo', true)->orderBy('precio')->get(),
            'promociones' => PromocionPublica::query()
                ->where('activo', true)
                ->where(fn ($query) => $query->whereNull('vigencia_inicio')->orWhere('vigencia_inicio', '<=', now()))
                ->where(fn ($query) => $query->whereNull('vigencia_fin')->orWhere('vigencia_fin', '>=', now()))
                ->orderBy('orden')
                ->get(),
        ])->layout('layouts.public.site');
    }
}

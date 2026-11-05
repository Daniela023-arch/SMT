<?php

namespace App\Livewire\Publico;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('MULTIVERSE | Inventario para ferreterías')]
class Inicio extends Component
{
    public function render()
    {
        return view('livewire.publico.inicio')->layout('layouts.public.site');
    }
}

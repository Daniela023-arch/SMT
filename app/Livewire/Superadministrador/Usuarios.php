<?php

namespace App\Livewire\Superadministrador;

use App\Models\Auditoria;
use App\Models\User;
use App\Services\AuditoriaService;
use Flux\Flux;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Usuarios globales | MULTIVERSE')]
class Usuarios extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroEstado = 'todos';
    public ?string $enlaceRestablecimiento = null;
    public ?string $emailRestablecimiento = null;

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function cambiarActivo(int $userId, AuditoriaService $auditoria): void
    {
        $user = User::query()->with('roles')->findOrFail($userId);
        $activar = ! (bool) $user->activo;

        if (! $activar && (int) $user->id === (int) auth()->id()) {
            $this->addError('usuario', 'No puedes desactivar la cuenta con la que tienes abierta esta sesión.');
            return;
        }

        $esSuperadmin = $user->roles->contains(fn ($role) => $role->slug === 'superadmin' && $role->tenant_id === null);
        if (! $activar && $esSuperadmin && User::query()->where('activo', true)->whereHas('roles', fn ($query) => $query
            ->where('roles.slug', 'superadmin')->whereNull('roles.tenant_id')->whereNull('usuario_rol.tenant_id'))->count() <= 1) {
            $this->addError('usuario', 'Debe quedar al menos un superadministrador activo.');
            return;
        }

        $antes = (bool) $user->activo;
        $user->forceFill(['activo' => $activar])->save();
        $auditoria->registrar((int) auth()->id(), $activar ? 'usuario_activado' : 'usuario_desactivado', 'User', $user->id, $user->tenant_id, ['activo' => $antes], ['activo' => $activar]);
        Flux::toast(variant: 'success', text: $activar ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function generarEnlaceRestablecimiento(int $userId, AuditoriaService $auditoria): void
    {
        $user = User::query()->findOrFail($userId);
        $token = Password::broker()->createToken($user);
        $this->emailRestablecimiento = $user->email;
        $this->enlaceRestablecimiento = URL::route('password.reset', ['token' => $token, 'email' => $user->email]);
        $auditoria->registrar((int) auth()->id(), 'enlace_restablecimiento_generado', 'User', $user->id, $user->tenant_id, null, ['email' => $user->email]);
    }

    public function cerrarEnlace(): void
    {
        $this->reset('enlaceRestablecimiento', 'emailRestablecimiento');
    }

    public function render()
    {
        $usuarios = User::query()
            ->with(['tenant:id,nombre', 'roles'])
            ->when($this->filtroEstado === 'activo', fn ($query) => $query->where('activo', true))
            ->when($this->filtroEstado === 'inactivo', fn ($query) => $query->where('activo', false))
            ->when($this->buscar !== '', fn ($query) => $query->where(fn ($users) => $users
                ->where('name', 'like', '%'.$this->buscar.'%')
                ->orWhere('email', 'like', '%'.$this->buscar.'%')
                ->orWhereHas('tenant', fn ($tenant) => $tenant->where('nombre', 'like', '%'.$this->buscar.'%'))))
            ->orderBy('name')
            ->paginate(15);

        $totalActivos = User::query()->where('activo', true)->count();
        $totalGlobales = User::query()->whereHas('roles', fn ($query) => $query->where('roles.slug', 'superadmin')->whereNull('roles.tenant_id'))->count();

        return view('livewire.superadministrador.usuarios', compact('usuarios', 'totalActivos', 'totalGlobales'))->layout('layouts.superadministrador');
    }
}

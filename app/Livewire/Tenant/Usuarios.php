<?php

namespace App\Livewire\Tenant;

use App\Models\Almacen;
use App\Models\User;
use App\Services\UsuarioTenantService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Personal del tenant | MULTIVERSE')]
class Usuarios extends Component
{
    public ?int $usuarioSeleccionado = null;

    public ?int $usuarioEnlaceId = null;

    public bool $mostrarFormulario = false;

    public string $nombre = '';

    public string $email = '';

    public string $rol = 'ventas';

    public array $establecimientos = [];

    public array $almacenes = [];

    public string $almacenVentaId = '';

    public bool $activo = true;

    public bool $puedeVerPuntoVenta = true;

    public bool $puedeVerAlmacen = true;

    public bool $personalHabilitado = true;

    public ?string $enlaceInicial = null;

    public ?string $enlaceRestablecimiento = null;

    public ?string $emailEnlace = null;

    public string $buscar = '';

    public string $filtroRol = 'todos';

    public function mount(UsuarioTenantService $service): void
    {
        abort_unless($service->puedeAdministrarPersonal(auth()->user()), 403);
        $this->personalHabilitado = $service->personalHabilitado((int) auth()->user()->tenant_id);
    }

    public function updatedRol(): void
    {
        $this->almacenes = [];
        $this->almacenVentaId = '';
        if ($this->rol !== 'gerente') {
            $this->puedeVerPuntoVenta = true;
            $this->puedeVerAlmacen = true;
        }
        $this->resetValidation('rol', 'almacenes');
    }

    public function abrirFormulario(): void
    {
        $this->resetForm();
        $this->mostrarFormulario = true;
    }

    public function editar(int $usuarioId, UsuarioTenantService $service): void
    {
        $usuario = $service->usuarios(auth()->user())->firstWhere('id', $usuarioId);
        abort_if($usuario === null, 404);

        $this->usuarioSeleccionado = (int) $usuario->getKey();
        $this->nombre = $usuario->name;
        $this->email = $usuario->email;
        $this->rol = $usuario->roles->first()?->slug ?? 'ventas';
        $this->activo = (bool) $usuario->activo;
        $this->puedeVerPuntoVenta = (bool) $usuario->puede_ver_punto_venta;
        $this->puedeVerAlmacen = (bool) $usuario->puede_ver_almacen;
        $this->establecimientos = $usuario->establecimientos->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->almacenes = $usuario->almacenesAsignados->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->almacenVentaId = $this->rol === 'ventas' ? (string) ($this->almacenes[0] ?? '') : '';
        $this->reset('enlaceInicial', 'enlaceRestablecimiento', 'emailEnlace');
        $this->usuarioEnlaceId = null;
        $this->mostrarFormulario = true;
        $this->resetValidation();
    }

    public function guardar(UsuarioTenantService $service): void
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->usuarioSeleccionado)],
            'rol' => ['required', Rule::in(auth()->user()->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists() ? ['ventas', 'almacen'] : ['gerente', 'ventas', 'almacen'])],
            'establecimientos' => $this->rol === 'ventas' ? ['nullable', 'array'] : ['required', 'array', 'min:1'],
            'establecimientos.*' => ['required', 'integer', 'distinct', 'exists:establecimientos,id'],
            'almacenes' => ['array'],
            'almacenes.*' => ['integer', 'distinct', 'exists:almacenes,id'],
            'almacenVentaId' => ['required_if:rol,ventas', 'nullable', 'integer', 'exists:almacenes,id'],
            'activo' => ['required', 'boolean'],
            'puedeVerPuntoVenta' => ['required_if:rol,gerente', 'boolean'],
            'puedeVerAlmacen' => ['required_if:rol,gerente', 'boolean'],
        ]);
        $datos['almacenes'] = $this->rol === 'ventas' ? [(int) $datos['almacenVentaId']] : $datos['almacenes'];
        $datos['puede_ver_punto_venta'] = (bool) ($datos['puedeVerPuntoVenta'] ?? true);
        $datos['puede_ver_almacen'] = (bool) ($datos['puedeVerAlmacen'] ?? true);

        $editando = $this->usuarioSeleccionado !== null;
        $resultado = $service->guardar(auth()->user(), $datos, $this->usuarioSeleccionado);
        $enlaceInicial = $resultado['enlaceInicial'];
        $emailNuevo = $resultado['usuario']->email;
        $this->resetForm();
        if (! $editando) {
            $this->enlaceInicial = $enlaceInicial;
            $this->emailEnlace = $emailNuevo;
        }
        Flux::toast(variant: 'success', text: $editando ? 'Cambios del usuario guardados.' : 'Usuario agregado al tenant.');
    }

    public function alternarActivo(int $usuarioId, UsuarioTenantService $service): void
    {
        try {
            $activo = $service->cambiarActivo(auth()->user(), $usuarioId);
            Flux::toast(variant: 'success', text: $activo ? 'Usuario activado.' : 'Usuario desactivado.');
        } catch (ValidationException $exception) {
            $this->addError('usuario', collect($exception->errors())->flatten()->first());
        }
    }

    public function restablecer(int $usuarioId, UsuarioTenantService $service): void
    {
        $usuario = $service->usuarios(auth()->user())->firstWhere('id', $usuarioId);
        abort_if($usuario === null, 404);
        $this->mostrarFormulario = false;
        $this->enlaceInicial = null;
        $this->enlaceRestablecimiento = $service->enlaceRestablecimiento(auth()->user(), $usuarioId);
        $this->emailEnlace = $usuario->email;
        $this->usuarioEnlaceId = $usuarioId;
        Flux::toast(variant: 'success', text: 'Enlace creado; aparece junto al usuario.');
    }

    public function alternarGestionPersonal(UsuarioTenantService $service): void
    {
        abort_unless(auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists(), 403);
        try {
            $service->cambiarPersonalHabilitado(auth()->user(), ! $this->personalHabilitado);
            $this->personalHabilitado = ! $this->personalHabilitado;
            Flux::toast(variant: 'success', text: $this->personalHabilitado ? 'Gestión de personal habilitada.' : 'Gestión de personal deshabilitada.');
        } catch (ValidationException $exception) {
            $this->addError('personal', collect($exception->errors())->flatten()->first());
        }
    }

    public function cancelarEdicion(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('usuarioSeleccionado', 'usuarioEnlaceId', 'nombre', 'email', 'establecimientos', 'almacenes', 'almacenVentaId', 'enlaceInicial', 'enlaceRestablecimiento', 'emailEnlace');
        $this->rol = 'ventas';
        $this->activo = true;
        $this->puedeVerPuntoVenta = true;
        $this->puedeVerAlmacen = true;
        $this->mostrarFormulario = false;
        $this->resetValidation();
    }

    public function render(UsuarioTenantService $service)
    {
        $actor = auth()->user();
        $esAdmin = $actor->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $actor->tenant_id)->exists();
        $branchOptions = $service->sucursalesDisponibles($actor);
        $warehouseQuery = Almacen::query()->where('tenant_id', $actor->tenant_id)->where('activo', true)
            ->whereIn('establecimiento_id', $this->establecimientos === [] ? [-1] : $this->establecimientos)->orderBy('nombre');
        $usuarios = $service->usuarios($actor)
            ->when($this->buscar !== '', fn ($collection) => $collection->filter(
                fn (User $user) => str_contains(mb_strtolower($user->name.' '.$user->email), mb_strtolower($this->buscar)),
            ))
            ->when($this->filtroRol !== 'todos', fn ($collection) => $collection->filter(fn (User $user) => $user->roles->contains('slug', $this->filtroRol)));

        return view('livewire.tenant.usuarios', [
            'usuarios' => $usuarios,
            'branchOptions' => $branchOptions,
            'warehouseOptions' => $this->rol === 'ventas'
                ? Almacen::query()->where('tenant_id', $actor->tenant_id)->where('activo', true)
                    ->whereIn('establecimiento_id', $branchOptions->modelKeys())->with('establecimiento:id,nombre')->orderBy('nombre')->get(['id', 'nombre', 'codigo', 'establecimiento_id'])
                : $warehouseQuery->get(['id', 'nombre', 'codigo', 'establecimiento_id']),
            'esAdmin' => $esAdmin,
        ])->layout('layouts.app');
    }
}

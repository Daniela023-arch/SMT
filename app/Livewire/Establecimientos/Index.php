<?php

namespace App\Livewire\Establecimientos;

use App\Models\Almacen;
use App\Models\Establecimiento;
use App\Models\Ubicacion;
use App\Services\EstablecimientoService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Establecimientos | MULTIVERSE')]
class Index extends Component
{
    public string $codigo = '';

    public string $nombre = '';

    public string $tipo = 'sucursal';

    public string $direccion = '';

    public int $establecimientoSeleccionado = 0;

    public string $codigoAlmacen = '';

    public string $nombreAlmacen = '';

    public int $almacenSeleccionado = 0;

    public string $nombreUbicacion = '';

    public string $codigoUbicacion = '';

    public int $usuarioId = 0;

    public function crear(EstablecimientoService $service): void
    {
        $this->autorizarAdmin();
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('establecimientos', 'codigo')->where('tenant_id', $tenantId)],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['sucursal', 'centro_distribucion'])],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ]);

        $establecimiento = $service->crear($tenantId, $datos);
        $this->establecimientoSeleccionado = (int) $establecimiento->getKey();
        $this->reset('codigo', 'nombre', 'direccion');
        Flux::toast(variant: 'success', text: 'Establecimiento creado.');
    }

    public function crearAlmacen(EstablecimientoService $service): void
    {
        $this->autorizarAdmin();
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'establecimientoSeleccionado' => ['required', 'integer', 'min:1'],
            'codigoAlmacen' => ['required', 'string', 'max:40', Rule::unique('almacenes', 'codigo')->where('establecimiento_id', $this->establecimientoSeleccionado)],
            'nombreAlmacen' => ['required', 'string', 'max:255'],
        ]);

        $service->crearAlmacen($tenantId, $datos['establecimientoSeleccionado'], $datos['codigoAlmacen'], $datos['nombreAlmacen']);
        $this->reset('codigoAlmacen', 'nombreAlmacen');
        Flux::toast(variant: 'success', text: 'Almacén creado con ubicación principal.');
    }

    public function crearUbicacion(): void
    {
        abort_unless(auth()->user()->roles()
            ->whereIn('roles.slug', ['admin', 'almacen'])
            ->wherePivot('tenant_id', auth()->user()->tenant_id)
            ->exists(), 403);

        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'almacenSeleccionado' => ['required', 'integer', 'min:1'],
            'nombreUbicacion' => ['required', 'string', 'max:255'],
            'codigoUbicacion' => ['nullable', 'string', 'max:255', Rule::unique('ubicaciones', 'codigo')->where('tenant_id', $tenantId)],
        ]);
        $almacen = Almacen::query()->where('tenant_id', $tenantId)->findOrFail($datos['almacenSeleccionado']);
        $this->autorizarAccesoAlmacen($almacen);

        Ubicacion::query()->create([
            'tenant_id' => $tenantId,
            'almacen_id' => $almacen->getKey(),
            'nombre' => $datos['nombreUbicacion'],
            'codigo' => $datos['codigoUbicacion'] ?: null,
        ]);

        $this->reset('nombreUbicacion', 'codigoUbicacion');
        Flux::toast(variant: 'success', text: 'Ubicación creada.');
    }

    public function asignarUsuario(EstablecimientoService $service): void
    {
        $this->autorizarAdmin();
        $datos = $this->validate([
            'establecimientoSeleccionado' => ['required', 'integer', 'min:1'],
            'usuarioId' => ['required', 'integer', 'exists:users,id'],
        ]);

        $service->asignarUsuario(
            (int) auth()->user()->tenant_id,
            $datos['usuarioId'],
            $datos['establecimientoSeleccionado'],
            (int) auth()->id(),
        );

        $this->reset('usuarioId');
        Flux::toast(variant: 'success', text: 'Usuario asignado al establecimiento.');
    }

    private function autorizarAdmin(): void
    {
        abort_unless(auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists(), 403);
    }

    private function autorizarAccesoAlmacen(Almacen $almacen): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        abort_unless($almacen->activo && $almacen->establecimiento()->where('activo', true)->exists(), 404);
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())->where('establecimiento_id', $almacen->establecimiento_id)->exists();
        abort_unless($admin || $assigned, 404);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $query = Establecimiento::query()->where('tenant_id', $tenantId)->with('almacenes.ubicaciones')->orderBy('nombre');
        if (! auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists()) {
            $query->whereHas('usuarios', fn ($users) => $users->where('users.id', auth()->id()));
        }

        return view('livewire.establecimientos.index', [
            'establecimientos' => $query->get(),
            'usuarios' => auth()->user()->tenant->usuarios()->orderBy('name')->get(['users.id', 'name', 'email']),
        ])->layout('layouts.app');
    }
}

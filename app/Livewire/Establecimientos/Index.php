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

    public function mount(): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $sales = $user->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', $tenantId)->exists();
        if ($sales) {
            $point = DB::table('usuario_almacen')->join('almacenes', 'almacenes.id', '=', 'usuario_almacen.almacen_id')
                ->join('establecimientos', 'establecimientos.id', '=', 'almacenes.establecimiento_id')
                ->where('usuario_almacen.tenant_id', $tenantId)->where('usuario_almacen.usuario_id', $user->getKey())
                ->where('almacenes.tenant_id', $tenantId)->where('almacenes.activo', true)->where('establecimientos.activo', true)
                ->first(['almacenes.id', 'almacenes.establecimiento_id']);
            if ($point === null) {
                $this->redirectRoute('dashboard', navigate: true);

                return;
            }
            $this->redirectRoute('ventas.index', ['establecimiento' => $point->establecimiento_id, 'almacen' => $point->id], navigate: true);
        }
    }

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
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'establecimientoSeleccionado' => ['required', 'integer', 'min:1'],
            'codigoAlmacen' => ['required', 'string', 'max:40', Rule::unique('almacenes', 'codigo')->where('establecimiento_id', $this->establecimientoSeleccionado)],
            'nombreAlmacen' => ['required', 'string', 'max:255'],
        ]);

        $this->autorizarAdminOGerenteEnSucursal((int) $datos['establecimientoSeleccionado']);

        $service->crearAlmacen($tenantId, $datos['establecimientoSeleccionado'], $datos['codigoAlmacen'], $datos['nombreAlmacen']);
        $this->reset('codigoAlmacen', 'nombreAlmacen');
        Flux::toast(variant: 'success', text: 'Almacén creado con ubicación principal.');
    }

    public function crearUbicacion(): void
    {
        abort_unless(auth()->user()->roles()
            ->whereIn('roles.slug', ['admin', 'gerente', 'almacen'])
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
        $gerente = $user->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())->where('establecimiento_id', $almacen->establecimiento_id)->exists();
        $assignedWarehouse = DB::table('usuario_almacen')->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())->where('almacen_id', $almacen->getKey())->exists();
        $isWarehouseOperator = $user->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists();
        abort_unless($admin || ($gerente && $assigned) || ($isWarehouseOperator && $assignedWarehouse), 404);
    }

    private function autorizarAdminOGerenteEnSucursal(int $establecimientoId): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $gerente = $user->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)
            ->where('usuario_id', $user->getKey())->where('establecimiento_id', $establecimientoId)->exists();
        abort_unless($admin || ($gerente && $assigned), 403);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $user = auth()->user();
        $isAdmin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $isWarehouseOperator = $user->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists();
        $query = Establecimiento::query()->where('tenant_id', $tenantId)
            ->with(['almacenes' => function ($warehouses) use ($user, $tenantId, $isWarehouseOperator, $isAdmin): void {
                $warehouses->with('ubicaciones');
                if ($isWarehouseOperator && ! $isAdmin) {
                    $warehouses->whereHas('usuarios', fn ($users) => $users->where('users.id', $user->getKey())->where('usuario_almacen.tenant_id', $tenantId));
                }
            }])->orderBy('nombre');
        if (! $isAdmin) {
            $query->whereHas('usuarios', fn ($users) => $users->where('users.id', $user->getKey())->where('usuario_establecimiento.tenant_id', $tenantId));
        }

        return view('livewire.establecimientos.index', [
            'establecimientos' => $query->get(),
        ])->layout('layouts.app');
    }
}

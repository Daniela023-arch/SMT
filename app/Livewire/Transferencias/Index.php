<?php

namespace App\Livewire\Transferencias;

use App\Models\Almacen;
use App\Models\Transferencia;
use App\Services\TransferenciaService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Transferencias | MULTIVERSE')]
class Index extends Component
{
    use WithPagination;

    public int $almacenOrigenId = 0;

    public int $almacenDestinoId = 0;

    public array $lineas = [['producto_id' => '', 'cantidad' => '']];

    public string $motivo = '';

    public string $motivoRechazo = '';

    public array $recepciones = [];

    public function agregarLinea(): void
    {
        $this->lineas[] = ['producto_id' => '', 'cantidad' => ''];
    }

    public function quitarLinea(int $indice): void
    {
        if (count($this->lineas) > 1) {
            unset($this->lineas[$indice]);
            $this->lineas = array_values($this->lineas);
        }
    }

    public function solicitar(TransferenciaService $service): void
    {
        $datos = $this->validate([
            'almacenOrigenId' => ['required', 'integer', 'min:1'],
            'almacenDestinoId' => ['required', 'integer', 'different:almacenOrigenId'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'min:1'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,4'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->solicitar(
            (int) auth()->user()->tenant_id,
            (int) auth()->id(),
            $datos['almacenOrigenId'],
            $datos['almacenDestinoId'],
            array_map(fn ($linea) => [
                'producto_id' => (int) $linea['producto_id'],
                'cantidad' => (float) $linea['cantidad'],
            ], $datos['lineas']),
            $datos['motivo'] ?: null,
        );
        $this->reset('motivo', 'lineas');
        $this->lineas = [['producto_id' => '', 'cantidad' => '']];
        Flux::toast(variant: 'success', text: 'Transferencia solicitada y enviada a autorización.');
    }

    public function autorizar(int $id, TransferenciaService $service): void
    {
        $this->autorizarAdminOGerente();
        $service->autorizar($this->transferenciaDelTenant($id)->getKey(), (int) auth()->id());
        Flux::toast(variant: 'success', text: 'Transferencia autorizada.');
    }

    public function rechazar(int $id, TransferenciaService $service): void
    {
        $this->autorizarAdminOGerente();
        $datos = $this->validate(['motivoRechazo' => ['required', 'string', 'max:1000']]);
        $service->rechazar($this->transferenciaDelTenant($id)->getKey(), (int) auth()->id(), $datos['motivoRechazo']);
        $this->reset('motivoRechazo');
        Flux::toast(variant: 'success', text: 'Transferencia rechazada.');
    }

    public function despachar(int $id, TransferenciaService $service): void
    {
        $this->autorizarOperaciones();
        $service->despachar($this->transferenciaDelTenant($id)->getKey(), (int) auth()->id());
        Flux::toast(variant: 'success', text: 'Salida registrada; transferencia en tránsito.');
    }

    public function recibir(int $id, TransferenciaService $service): void
    {
        $this->autorizarOperaciones();
        $datos = $this->validate(['recepciones' => ['required', 'array', 'min:1'], 'recepciones.*' => ['nullable', 'numeric', 'gte:0', 'decimal:0,4']]);
        $cantidades = array_filter($datos['recepciones'], fn ($value) => is_numeric($value) && (float) $value > 0);
        if ($cantidades === []) {
            $this->addError('recepciones', 'Captura al menos una cantidad recibida.');

            return;
        }
        $service->recibir($this->transferenciaDelTenant($id)->getKey(), (int) auth()->id(), $cantidades);
        $this->reset('recepciones');
        Flux::toast(variant: 'success', text: 'Recepción registrada.');
    }

    public function cancelar(int $id, TransferenciaService $service): void
    {
        $this->autorizarAdminOGerente();
        $service->cancelar($this->transferenciaDelTenant($id)->getKey(), (int) auth()->id());
        Flux::toast(variant: 'success', text: 'Transferencia cancelada.');
    }

    private function transferenciaDelTenant(int $id): Transferencia
    {
        $transferencia = Transferencia::query()->where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        abort_unless($this->tieneAccesoAlmacen((int) $transferencia->almacen_origen_id)
            || $this->tieneAccesoAlmacen((int) $transferencia->almacen_destino_id), 404);

        return $transferencia;
    }

    private function autorizarAdminOGerente(): void
    {
        abort_unless(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'gerente'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists(), 403);
    }

    private function autorizarOperaciones(): void
    {
        abort_unless(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'gerente', 'almacen'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists(), 403);
    }

    private function tieneAccesoAlmacen(int $almacenId): bool
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $almacen = Almacen::query()->where('tenant_id', $tenantId)->find($almacenId);
        if (! $almacen) return false;
        if ($user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists()) return true;
        if ($user->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists()) {
            return DB::table('usuario_almacen')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())->where('almacen_id', $almacenId)->exists();
        }

        return DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())->where('establecimiento_id', $almacen->establecimiento_id)->exists();
    }

    public function puedeOperarAlmacen(int $almacenId): bool
    {
        return $this->tieneAccesoAlmacen($almacenId);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $admin = auth()->user()->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $query = Transferencia::query()->where('tenant_id', $tenantId)
            ->with(['almacenOrigen:id,codigo,nombre', 'almacenDestino:id,codigo,nombre', 'solicitante:id,name', 'detalles.producto:id,sku,nombre'])
            ->latest('id');
        if (! $admin) {
            $branchIds = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', auth()->id())->select('establecimiento_id');
            $warehouseIds = DB::table('usuario_almacen')->where('tenant_id', $tenantId)->where('usuario_id', auth()->id())->select('almacen_id');
            $esAlmacen = auth()->user()->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists();
            $query->where(function ($scope) use ($esAlmacen, $warehouseIds, $branchIds): void {
                if ($esAlmacen) {
                    $scope->whereIn('almacen_origen_id', $warehouseIds)->orWhereIn('almacen_destino_id', $warehouseIds);
                } else {
                    $scope->whereIn('establecimiento_origen_id', $branchIds)->orWhereIn('establecimiento_destino_id', $branchIds);
                }
            });
        }

        $almacenesQuery = Almacen::query()->where('tenant_id', $tenantId)->where('activo', true)->with('establecimiento:id,nombre')->orderBy('nombre');
        if (! $admin) {
            $esAlmacen = auth()->user()->roles()->where('roles.slug', 'almacen')->wherePivot('tenant_id', $tenantId)->exists();
            $esGerente = auth()->user()->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists();
            if ($esAlmacen) $almacenesQuery->whereIn('id', DB::table('usuario_almacen')->where('tenant_id', $tenantId)->where('usuario_id', auth()->id())->select('almacen_id'));
            elseif ($esGerente) $almacenesQuery->whereIn('establecimiento_id', DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', auth()->id())->select('establecimiento_id'));
        }

        return view('livewire.transferencias.index', [
            'transferencias' => $query->paginate(20),
            'almacenes' => $almacenesQuery->get(),
            'productos' => auth()->user()->tenant->productos()->where('activo', true)->orderBy('nombre')->get(['productos.id', 'sku', 'nombre']),
        ])->layout('layouts.app');
    }
}

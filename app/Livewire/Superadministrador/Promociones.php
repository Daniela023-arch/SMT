<?php

namespace App\Livewire\Superadministrador;

use App\Models\AsignacionPromocion;
use App\Models\PromocionPublica;
use App\Models\PromocionSuscripcion;
use App\Models\SuscripcionTenant;
use App\Services\AuditoriaService;
use App\Services\PromocionService;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Promociones | MULTIVERSE')]
class Promociones extends Component
{
    public ?int $promocionPublicaId = null;
    public string $titulo = '';
    public string $etiqueta = '';
    public string $descripcion = '';
    public string $textoBoton = '';
    public string $urlBoton = '';
    public string $orden = '0';
    public string $vigenciaInicio = '';
    public string $vigenciaFin = '';

    public string $codigo = '';
    public string $tipoDescuento = 'porcentaje';
    public string $valorDescuento = '';
    public string $inicioDescuento = '';
    public string $finDescuento = '';
    public string $usosMaximos = '';
    public int $suscripcionId = 0;
    public int $promocionIdAsignar = 0;

    public function guardarPublica(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'etiqueta' => ['nullable', 'string', 'max:40'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'textoBoton' => ['nullable', 'string', 'max:60'],
            'urlBoton' => ['nullable', 'url', 'max:255'],
            'orden' => ['required', 'integer', 'min:0', 'max:65535'],
            'vigenciaInicio' => ['nullable', 'date'],
            'vigenciaFin' => ['nullable', 'date', 'after_or_equal:vigenciaInicio'],
        ]);

        $atributos = [
            'titulo' => $datos['titulo'], 'etiqueta' => $datos['etiqueta'] ?: null,
            'descripcion' => $datos['descripcion'], 'texto_boton' => $datos['textoBoton'] ?: null,
            'url_boton' => $datos['urlBoton'] ?: null, 'orden' => $datos['orden'],
            'vigencia_inicio' => $datos['vigenciaInicio'] ?: null, 'vigencia_fin' => $datos['vigenciaFin'] ?: null,
            'actualizado_por' => auth()->id(),
        ];

        if ($this->promocionPublicaId) {
            $promo = PromocionPublica::query()->findOrFail($this->promocionPublicaId);
            $antes = $promo->only(['titulo', 'etiqueta', 'descripcion', 'texto_boton', 'url_boton', 'orden', 'activo']);
            $promo->update($atributos);
        } else {
            $promo = PromocionPublica::query()->create($atributos + ['activo' => true]);
            $antes = null;
        }

        $auditoria->registrar((int) auth()->id(), 'promocion_publica_guardada', 'PromocionPublica', $promo->id, null, $antes, $atributos);
        $this->cancelarPublica();
        Flux::toast(variant: 'success', text: 'Promoción pública guardada.');
    }

    public function editarPublica(int $id): void
    {
        $promo = PromocionPublica::query()->findOrFail($id);
        $this->promocionPublicaId = $promo->id;
        $this->titulo = $promo->titulo;
        $this->etiqueta = $promo->etiqueta ?? '';
        $this->descripcion = $promo->descripcion;
        $this->textoBoton = $promo->texto_boton ?? '';
        $this->urlBoton = $promo->url_boton ?? '';
        $this->orden = (string) $promo->orden;
        $this->vigenciaInicio = $promo->vigencia_inicio?->format('Y-m-d\TH:i') ?? '';
        $this->vigenciaFin = $promo->vigencia_fin?->format('Y-m-d\TH:i') ?? '';
    }

    public function alternarPublica(int $id, AuditoriaService $auditoria): void
    {
        $promo = PromocionPublica::query()->findOrFail($id);
        $antes = $promo->activo;
        $promo->update(['activo' => ! $promo->activo, 'actualizado_por' => auth()->id()]);
        $auditoria->registrar((int) auth()->id(), 'promocion_publica_estado', 'PromocionPublica', $promo->id, null, ['activo' => $antes], ['activo' => $promo->activo]);
    }

    public function guardarCodigo(AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('promociones_suscripcion', 'codigo')],
            'tipoDescuento' => ['required', Rule::in(['porcentaje', 'monto'])],
            'valorDescuento' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'inicioDescuento' => ['nullable', 'date'],
            'finDescuento' => ['nullable', 'date', 'after_or_equal:inicioDescuento'],
            'usosMaximos' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($datos['tipoDescuento'] === 'porcentaje' && (float) $datos['valorDescuento'] > 100) {
            $this->addError('valorDescuento', 'El descuento porcentual no puede superar el 100%.');
            return;
        }

        $promo = PromocionSuscripcion::query()->create([
            'codigo' => Str::upper(trim($datos['codigo'])),
            'tipo' => $datos['tipoDescuento'],
            'valor' => $datos['valorDescuento'],
            'vigencia_inicio' => $datos['inicioDescuento'] ?: null,
            'vigencia_fin' => $datos['finDescuento'] ?: null,
            'usos_maximos' => $datos['usosMaximos'] ?: null,
            'activo' => true,
        ]);
        $auditoria->registrar((int) auth()->id(), 'codigo_promocional_creado', 'PromocionSuscripcion', $promo->id, null, null, $promo->only(['codigo', 'tipo', 'valor', 'usos_maximos']));
        $this->reset('codigo', 'valorDescuento', 'inicioDescuento', 'finDescuento', 'usosMaximos');
        $this->tipoDescuento = 'porcentaje';
        Flux::toast(variant: 'success', text: 'Código promocional creado.');
    }

    public function alternarCodigo(int $id, AuditoriaService $auditoria): void
    {
        $promo = PromocionSuscripcion::query()->findOrFail($id);
        $antes = $promo->activo;
        $promo->update(['activo' => ! $promo->activo]);
        $auditoria->registrar((int) auth()->id(), 'codigo_promocional_estado', 'PromocionSuscripcion', $promo->id, null, ['activo' => $antes], ['activo' => $promo->activo]);
    }

    public function asignar(PromocionService $service, AuditoriaService $auditoria): void
    {
        $datos = $this->validate([
            'suscripcionId' => ['required', 'integer', 'exists:suscripciones_tenant,id'],
            'promocionIdAsignar' => ['required', 'integer', 'exists:promociones_suscripcion,id'],
        ]);
        $asignacion = $service->asignar((int) $datos['suscripcionId'], (string) PromocionSuscripcion::query()->findOrFail($datos['promocionIdAsignar'])->codigo);
        $auditoria->registrar((int) auth()->id(), 'promocion_suscripcion_asignada', 'AsignacionPromocion', $asignacion->id, (int) $asignacion->suscripcion?->tenant_id, null, ['promocion_id' => $datos['promocionIdAsignar'], 'suscripcion_id' => $datos['suscripcionId']]);
        Flux::toast(variant: 'success', text: 'Promoción asignada a la suscripción.');
    }

    public function cancelarPublica(): void
    {
        $this->reset('promocionPublicaId', 'titulo', 'etiqueta', 'descripcion', 'textoBoton', 'urlBoton', 'vigenciaInicio', 'vigenciaFin');
        $this->orden = '0';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.superadministrador.promociones', [
            'promocionesPublicas' => PromocionPublica::query()->orderBy('orden')->latest('id')->get(),
            'codigos' => PromocionSuscripcion::query()->withCount('asignacionesPromocion')->latest('id')->get(),
            'suscripciones' => SuscripcionTenant::query()->with(['tenant:id,nombre', 'plan:id,nombre'])->whereIn('estado', ['activa', 'prueba'])->latest('inicio_at')->get(),
            'promocionesActivas' => PromocionSuscripcion::query()->where('activo', true)->orderBy('codigo')->get(),
        ])->layout('layouts.superadministrador');
    }
}

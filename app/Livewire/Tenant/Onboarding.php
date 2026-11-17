<?php

namespace App\Livewire\Tenant;

use App\Models\ConfiguracionTenant;
use App\Models\SuscripcionTenant;
use App\Services\ImportadorCatalogoTenantService;
use App\Services\MercadoPagoSuscripcionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Onboarding extends Component
{
    use WithFileUploads;

    private const PALETAS = [
        'azul' => ['nombre' => 'Azul', 'primario' => '#2563eb', 'secundario' => '#0f172a'],
        'verde' => ['nombre' => 'Verde', 'primario' => '#047857', 'secundario' => '#064e3b'],
        'violeta' => ['nombre' => 'Violeta', 'primario' => '#7c3aed', 'secundario' => '#312e81'],
        'naranja' => ['nombre' => 'Naranja', 'primario' => '#c2410c', 'secundario' => '#7c2d12'],
    ];

    public int $paso = 1;

    public ?string $paletaSeleccionada = 'azul';

    public string $colorPrimario = '#2563eb';

    public string $colorSecundario = '#0f172a';

    public $logo;

    public $archivoCsv;

    public ?string $logoActual = null;

    public ?array $resultadoImportacion = null;

    public ?string $estadoPago = null;

    public ?string $errorPago = null;

    public function avanzar(ImportadorCatalogoTenantService $importador): void
    {
        if ($this->paso === 1) {
            $this->validate([
                'paletaSeleccionada' => ['required', Rule::in(array_keys(self::PALETAS))],
                'logo' => ['nullable', 'image', 'max:3072'],
            ]);

            $this->aplicarPaletaSeleccionada();

            $this->paso = 2;
            return;
        }

        if ($this->paso === 2) {
            $this->guardarPersonalizacion($importador);
            $this->paso = 3;
        }
    }

    public function regresarPaso(): void
    {
        $this->paso = max(1, $this->paso - 1);
    }

    public function seleccionarPaleta(string $paleta): void
    {
        if (! array_key_exists($paleta, self::PALETAS)) {
            $this->addError('paletaSeleccionada', 'Selecciona una de las cuatro paletas disponibles.');
            return;
        }

        $this->paletaSeleccionada = $paleta;
        $this->aplicarPaletaSeleccionada();
        $this->resetValidation('paletaSeleccionada');
    }

    private function aplicarPaletaSeleccionada(): void
    {
        $paleta = self::PALETAS[$this->paletaSeleccionada];
        $this->colorPrimario = $paleta['primario'];
        $this->colorSecundario = $paleta['secundario'];
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_if($user?->tenant_id === null, 403);
        $configuracion = ConfiguracionTenant::query()->firstOrCreate(
            ['tenant_id' => $user->tenant_id],
            ['preferencias' => ['personal_habilitado' => false]],
        );
        $this->colorPrimario = $configuracion->color_primario ?: $this->colorPrimario;
        $this->colorSecundario = $configuracion->color_secundario ?: $this->colorSecundario;
        $seleccion = collect(self::PALETAS)
            ->search(fn (array $paleta): bool => $paleta['primario'] === $this->colorPrimario && $paleta['secundario'] === $this->colorSecundario);
        $this->paletaSeleccionada = $seleccion === false ? null : $seleccion;
        $this->logoActual = $configuracion->logotipo;

        $subscription = SuscripcionTenant::query()->where('tenant_id', $user->tenant_id)->latest('id')->first();
        $this->estadoPago = $subscription?->estado;
    }

    public function guardarPersonalizacion(ImportadorCatalogoTenantService $importador): void
    {
        $this->validate([
            'paletaSeleccionada' => ['required', Rule::in(array_keys(self::PALETAS))],
            'colorPrimario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'colorSecundario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:3072'],
            'archivoCsv' => ['nullable', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'colorPrimario.regex' => 'Selecciona un color hexadecimal válido.',
            'colorSecundario.regex' => 'Selecciona un color hexadecimal válido.',
            'logo.max' => 'El logotipo no puede superar 3 MB.',
            'archivoCsv.max' => 'El CSV no puede superar 10 MB.',
        ]);

        $this->aplicarPaletaSeleccionada();

        $tenant = auth()->user()->tenant;
        $config = ConfiguracionTenant::query()->firstOrCreate(['tenant_id' => $tenant->getKey()]);
        $logoPath = $config->logotipo;
        if ($this->logo) {
            if ($logoPath && ! str_starts_with($logoPath, 'http')) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logo->store('tenants/'.$tenant->getKey().'/branding', 'public');
        }
        $config->forceFill([
            'color_primario' => $this->colorPrimario,
            'color_secundario' => $this->colorSecundario,
            'logotipo' => $logoPath,
            'preferencias' => array_merge($config->preferencias ?? [], ['personalizacion_inicial_guardada' => true]),
        ])->save();
        $this->logoActual = $logoPath;

        if ($this->archivoCsv) {
            $importacion = $importador->importar((int) $tenant->getKey(), auth()->user(), $this->archivoCsv);
            $this->resultadoImportacion = [
                'total' => $importacion->filas_totales,
                'exitosas' => $importacion->filas_exitosas,
                'fallidas' => $importacion->filas_fallidas,
                'errores' => array_slice($importacion->errores ?? [], 0, 10),
            ];
            $this->reset('archivoCsv');
        }
    }

    public function iniciarPago(MercadoPagoSuscripcionService $mercadoPago, ImportadorCatalogoTenantService $importador)
    {
        $this->guardarPersonalizacion($importador);
        $subscription = SuscripcionTenant::query()->with('plan')
            ->where('tenant_id', auth()->user()->tenant_id)->latest('id')->firstOrFail();

        try {
            $checkoutUrl = $mercadoPago->iniciar($subscription);
            return redirect()->away($checkoutUrl);
        } catch (ValidationException $exception) {
            $this->errorPago = collect($exception->errors())->flatten()->first();
            return null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorPago = 'No se pudo iniciar el cobro. Inténtalo de nuevo en unos minutos.';
            return null;
        }
    }

    public function revisarPago(MercadoPagoSuscripcionService $mercadoPago): void
    {
        $subscription = SuscripcionTenant::query()->where('tenant_id', auth()->user()->tenant_id)->latest('id')->firstOrFail();
        if ($subscription->proveedor !== 'mercadopago' || ! $subscription->suscripcion_externa_id) {
            $this->errorPago = 'Aún no hay un intento de pago para revisar.';
            return;
        }

        try {
            $remote = $mercadoPago->sincronizarSuscripcion($subscription->suscripcion_externa_id);
            $this->estadoPago = $remote['status'] ?? $subscription->estado;
            $this->errorPago = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorPago = 'No se pudo consultar Mercado Pago. La confirmación automática seguirá pendiente.';
        }
    }

    public function render()
    {
        $tenant = auth()->user()->tenant()->with('suscripciones.plan')->firstOrFail();
        $suscripcion = $tenant->suscripciones->sortByDesc('id')->first();

        return view('livewire.tenant.onboarding', [
            'tenant' => $tenant,
            'suscripcion' => $suscripcion,
            'paletas' => self::PALETAS,
        ])
            ->layout('layouts.tenant-onboarding', ['title' => 'Configura tu espacio | MULTIVERSE']);
    }
}

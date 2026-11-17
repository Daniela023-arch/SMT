<div class="min-h-screen bg-slate-50 px-4 py-10 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6">
    <div class="mx-auto max-w-4xl">
        <header class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Primer acceso</p>
            <h1 class="mt-2 text-3xl font-bold">Personaliza {{ $tenant->nombre }}</h1>
            <p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-300">Configura la identidad visual, importa tu catálogo si ya lo tienes y termina con el pago del plan para activar el espacio.</p>
        </header>

        <div class="mb-8 px-1" aria-label="Pasos de configuración">
            <div class="relative grid grid-cols-3">
                <div class="absolute left-[16.666%] right-[16.666%] top-5 h-1 rounded-full bg-slate-200 dark:bg-slate-700" aria-hidden="true">
                    <div class="h-full rounded-full bg-blue-600 transition-[width] duration-500 ease-out dark:bg-blue-400" style="width: {{ (($paso - 1) / 2) * 100 }}%"></div>
                </div>
                @foreach ([1 => 'Personalización', 2 => 'Catálogo', 3 => 'Pago y activación'] as $numero => $etiqueta)
                    @php($completado = $paso > $numero)
                    @php($actual = $paso === $numero)
                    <div class="relative flex flex-col items-center gap-2 text-center" @if($actual) aria-current="step" @endif>
                        <span class="grid size-10 place-items-center rounded-full border-2 text-sm font-bold transition-all duration-300 {{ $completado || $actual ? 'border-blue-700 bg-blue-700 text-white shadow-md shadow-blue-700/20 dark:border-blue-400 dark:bg-blue-500' : 'border-slate-300 bg-slate-50 text-slate-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-400' }}">{{ $numero }}</span>
                        <span class="text-xs font-semibold sm:text-sm {{ $actual ? 'text-blue-800 dark:text-blue-300' : ($completado ? 'text-slate-700 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400') }}">{{ $etiqueta }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($suscripcion)
            <section class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                @if ($paso === 1)
                <div>
                    <h2 class="text-lg font-semibold">Identidad de tu empresa</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Elige una de estas cuatro combinaciones para personalizar los colores del espacio.</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($paletas as $id => $paleta)
                            <button type="button" wire:click="seleccionarPaleta('{{ $id }}')" aria-pressed="{{ $paletaSeleccionada === $id ? 'true' : 'false' }}" class="flex min-h-20 items-center gap-4 rounded-xl border-2 p-4 text-left transition hover:-translate-y-0.5 hover:shadow-sm {{ $paletaSeleccionada === $id ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-100 dark:border-blue-400 dark:bg-blue-950/40 dark:ring-blue-900' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600' }}">
                                <span class="flex shrink-0 -space-x-2" aria-hidden="true">
                                    <span class="size-9 rounded-full border-2 border-white shadow-sm dark:border-slate-800" style="background-color: {{ $paleta['primario'] }}"></span>
                                    <span class="mt-2 size-7 rounded-full border-2 border-white shadow-sm dark:border-slate-800" style="background-color: {{ $paleta['secundario'] }}"></span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $paleta['nombre'] }}</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Principal y secundario</span>
                                </span>
                                @if ($paletaSeleccionada === $id)
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0 text-blue-700 dark:text-blue-300" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.004L3.29 9.208a1 1 0 1 1 1.42-1.408l4.09 4.126 6.49-6.642a1 1 0 0 1 1.414.006Z" clip-rule="evenodd"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @error('paletaSeleccionada') <span class="mt-2 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <label class="block text-sm font-semibold">Logotipo <span class="font-normal text-slate-500">(opcional, PNG/JPG/WebP)</span></label>
                    <input type="file" wire:model="logo" accept="image/*" class="mt-3 block w-full rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                    @error('logo') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="logo" class="mt-2 text-xs text-blue-700">Subiendo logotipo…</div>
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="Vista previa del logotipo" class="mt-3 h-16 max-w-48 rounded border border-slate-200 bg-white object-contain p-2">
                    @elseif ($logoActual)
                        <img src="{{ str_starts_with($logoActual, 'http') ? $logoActual : Storage::disk('public')->url($logoActual) }}" alt="Logotipo actual" class="mt-3 h-16 max-w-48 rounded border border-slate-200 bg-white object-contain p-2">
                    @endif
                </div>
                @endif

                @if ($paso === 2)
                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <h2 class="text-lg font-semibold">Carga masiva de productos <span class="text-sm font-normal text-slate-500">(opcional)</span></h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sube un CSV de hasta 5,000 filas. SKU existente se actualiza en este tenant; filas inválidas se reportan sin detener las demás.</p>
                    <p class="mt-2 text-xs text-slate-500">Encabezados: <code class="rounded bg-slate-100 px-1 py-0.5 dark:bg-slate-800">sku,nombre,unidad_medida,precio_venta,stock_minimo,descripcion</code></p>
                    <input type="file" wire:model="archivoCsv" accept=".csv,text/csv" class="mt-3 block w-full rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                    @error('archivoCsv') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    @if ($resultadoImportacion)
                        <div class="mt-3 rounded-lg bg-blue-50 p-4 text-sm text-blue-950 dark:bg-blue-950 dark:text-blue-100">
                            Importación: {{ $resultadoImportacion['exitosas'] }} de {{ $resultadoImportacion['total'] }} filas procesadas correctamente; {{ $resultadoImportacion['fallidas'] }} con error.
                            @if ($resultadoImportacion['errores'])
                                <ul class="mt-2 list-inside list-disc text-xs">@foreach ($resultadoImportacion['errores'] as $error)<li>Fila {{ $error['fila'] }}: {{ $error['mensaje'] }}</li>@endforeach</ul>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                @if ($paso === 3)
                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Activar el plan {{ $suscripcion->plan->nombre }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">${{ number_format((float) $suscripcion->plan->precio, 2) }} {{ $suscripcion->plan->moneda }} / {{ $suscripcion->plan->periodicidad }}</p>
                            <p class="mt-2 text-xs text-slate-500">El acceso se habilita únicamente cuando Mercado Pago confirme el primer pago.</p>
                        </div>
                        @if ($suscripcion->estado === 'activa' && $tenant->estado === 'activo')
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800">Ir al panel</a>
                        @else
                            <button type="button" wire:click="iniciarPago" wire:loading.attr="disabled" class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                                <span wire:loading.remove wire:target="iniciarPago">Guardar y continuar al pago</span>
                                <span wire:loading wire:target="iniciarPago">Preparando pago…</span>
                            </button>
                        @endif
                    </div>
                    @if (app()->isLocal() && ! ($suscripcion->estado === 'activa' && $tenant->estado === 'activo'))
                        <a href="{{ route('onboarding.simulacion') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-800 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200 dark:hover:bg-blue-900">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            Vista previa del checkout (simulación local)
                        </a>
                    @endif
                    @if ($errorPago)<p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950 dark:text-red-200">{{ $errorPago }}</p>@endif
                    @if ($suscripcion->suscripcion_externa_id && ! ($suscripcion->estado === 'activa' && $tenant->estado === 'activo'))
                        <div class="mt-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-100">
                            El pago está en proceso. La activación se confirmará automáticamente al recibir el evento de pago aprobado.
                            <button type="button" wire:click="revisarPago" class="ml-2 font-semibold underline">Revisar estado</button>
                            @if ($estadoPago)<span class="ml-2 text-xs">Estado reportado: {{ $estadoPago }}</span>@endif
                        </div>
                    @endif
                </div>
                @endif

                <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-6 dark:border-slate-800">
                    @if ($paso > 1)
                        <button type="button" wire:click="regresarPaso" class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Anterior</button>
                    @else
                        <span></span>
                    @endif
                    @if ($paso < 3)
                        <button type="button" wire:click="avanzar" wire:loading.attr="disabled" class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:opacity-60">
                            <span wire:loading.remove wire:target="avanzar">{{ $paso === 1 ? 'Continuar' : 'Guardar y continuar' }}</span>
                            <span wire:loading wire:target="avanzar">Guardando…</span>
                        </button>
                    @endif
                </div>
            </section>
        @else
            <div class="rounded-xl bg-amber-50 p-5 text-amber-900 dark:bg-amber-950 dark:text-amber-100">No hay un plan asignado a este espacio. Contacta al superadministrador.</div>
        @endif
    </div>
</div>

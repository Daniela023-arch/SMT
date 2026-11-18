<main class="min-h-[75vh] bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:py-14 md:px-8">
        @if ($folio)
            <section class="mx-auto max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600"></div>
                <div class="p-7 text-center sm:p-12">
                    <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-50 text-3xl font-bold text-emerald-700 ring-8 ring-emerald-50/60" aria-hidden="true">✓</span>
                    <p class="mt-7 text-xs font-bold uppercase tracking-[.18em] text-emerald-700">Solicitud recibida</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Gracias por elegir MULTIVERSE</h1>
                    <p class="mx-auto mt-4 max-w-lg leading-7 text-slate-600">Nuestro equipo revisará los datos de tu ferretería y se pondrá en contacto para definir el plan y la configuración que necesitas.</p>
                    <div class="mx-auto mt-8 max-w-md divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-slate-50 px-5 text-left">
                        <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm text-slate-500">Folio de seguimiento</span><strong class="font-mono text-sm font-bold text-slate-900">{{ $folio }}</strong></div>
                        <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm text-slate-500">Estado</span><span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800"><span class="size-1.5 rounded-full bg-amber-500"></span>Pendiente de revisión</span></div>
                    </div>
                    <p class="mt-5 text-xs leading-5 text-slate-500">Enviar la solicitud no genera un cargo ni activa una cuenta automáticamente.</p>
                    <a href="{{ route('home') }}" wire:navigate class="mt-7 inline-flex min-h-12 items-center justify-center rounded-xl bg-indigo-700 px-6 font-bold text-white shadow-sm transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">Volver al inicio</a>
                </div>
            </section>
        @else
            <div class="grid items-start gap-8 lg:grid-cols-[.78fr_1.22fr] lg:gap-12">
                <aside class="lg:sticky lg:top-28">
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-indigo-700"><span aria-hidden="true">←</span> Volver al inicio</a>
                    <p class="mt-8 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-800"><span class="size-2 rounded-full bg-emerald-500"></span>Hablemos de tu operación</p>
                    <h1 class="mt-5 max-w-xl text-4xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-5xl">Solicita MULTIVERSE para tu ferretería</h1>
                    <p class="mt-5 max-w-xl text-base leading-7 text-slate-600">Cuéntanos cómo trabaja tu negocio. Revisaremos tu solicitud y te ayudaremos a definir el plan, las sucursales y los módulos adecuados.</p>

                    <div class="mt-8 space-y-4">
                        @foreach ([['01', 'Comparte tus datos', 'Déjanos un medio de contacto y conoce tu negocio.'], ['02', 'Revisamos tu operación', 'Tomamos en cuenta sucursales, almacenes y necesidades.'], ['03', 'Definimos la configuración', 'Acordamos contigo el plan antes de activar tu espacio.']] as [$paso, $titulo, $detalle])
                            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-xs font-extrabold text-indigo-800">{{ $paso }}</span>
                                <div><h2 class="text-sm font-bold text-slate-900">{{ $titulo }}</h2><p class="mt-1 text-sm leading-5 text-slate-500">{{ $detalle }}</p></div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-5 flex gap-2 text-xs leading-5 text-slate-500"><span class="text-emerald-700" aria-hidden="true">●</span> La solicitud no genera cargos y no activa una cuenta automáticamente.</p>
                </aside>

                <form wire:submit="enviar" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                    <div class="border-b border-slate-100 px-6 py-6 sm:px-8">
                        <p class="text-xs font-bold uppercase tracking-[.16em] text-indigo-700">Solicitud de información</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Cuéntanos sobre tu negocio</h2>
                        <p class="mt-1 text-sm text-slate-500">Los campos marcados son necesarios para contactarte.</p>
                    </div>

                    <div class="space-y-7 p-6 sm:p-8">
                        <section aria-labelledby="datos-negocio">
                            <div class="mb-4 flex items-center gap-3"><span class="grid size-8 place-items-center rounded-lg bg-slate-100 text-xs font-extrabold text-slate-600">1</span><h3 id="datos-negocio" class="font-bold text-slate-900">Datos del negocio y contacto</h3></div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="grid gap-1.5 text-sm font-semibold text-slate-700">Nombre del negocio <span class="sr-only">(requerido)</span><input wire:model="negocio" autocomplete="organization" required aria-invalid="@error('negocio') true @else false @enderror" class="min-h-11 rounded-xl border-slate-300 bg-white px-3.5 text-sm font-normal text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('negocio') border-red-400 @enderror" placeholder="Ej. Ferretería Los Pinos">@error('negocio')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                                <label class="grid gap-1.5 text-sm font-semibold text-slate-700">Persona responsable<input wire:model="responsable" autocomplete="name" required aria-invalid="@error('responsable') true @else false @enderror" class="min-h-11 rounded-xl border-slate-300 bg-white px-3.5 text-sm font-normal text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('responsable') border-red-400 @enderror" placeholder="Nombre y apellido">@error('responsable')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                                <label class="grid gap-1.5 text-sm font-semibold text-slate-700">Correo electrónico<input type="email" wire:model="email" autocomplete="email" required aria-invalid="@error('email') true @else false @enderror" class="min-h-11 rounded-xl border-slate-300 bg-white px-3.5 text-sm font-normal text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('email') border-red-400 @enderror" placeholder="contacto@ferreteria.mx">@error('email')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                                <label class="grid gap-1.5 text-sm font-semibold text-slate-700">Teléfono de contacto<input type="tel" wire:model="telefono" autocomplete="tel" required aria-invalid="@error('telefono') true @else false @enderror" class="min-h-11 rounded-xl border-slate-300 bg-white px-3.5 text-sm font-normal text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('telefono') border-red-400 @enderror" placeholder="55 1234 5678">@error('telefono')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                            </div>
                        </section>

                        <section aria-labelledby="configuracion-negocio" class="border-t border-slate-100 pt-6">
                            <div class="mb-4 flex items-center gap-3"><span class="grid size-8 place-items-center rounded-lg bg-slate-100 text-xs font-extrabold text-slate-600">2</span><h3 id="configuracion-negocio" class="font-bold text-slate-900">Lo que necesitas</h3></div>
                            <fieldset>
                                <legend class="text-sm font-semibold text-slate-700">Periodicidad que te interesa</legend>
                                <p class="mt-1 text-xs text-slate-500">Es una referencia para la propuesta; no se realizará ningún cobro ahora.</p>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    @foreach (['Mensual','Trimestral','Anual'] as $opcion)
                                        <label class="cursor-pointer"><input class="peer sr-only" type="radio" wire:model="plan" value="{{ $opcion }}"><span class="flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-800 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-600">{{ $opcion }}</span></label>
                                    @endforeach
                                </div>
                                @error('plan')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </fieldset>
                            <label class="mt-5 grid gap-1.5 text-sm font-semibold text-slate-700">Describe brevemente tu operación<textarea wire:model="descripcion" rows="4" required aria-invalid="@error('descripcion') true @else false @enderror" class="rounded-xl border-slate-300 bg-white px-3.5 py-3 text-sm font-normal leading-6 text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('descripcion') border-red-400 @enderror" placeholder="Por ejemplo: número de sucursales, almacenes y qué necesitas controlar."></textarea><span class="text-xs font-normal text-slate-500">Incluye cualquier detalle que nos ayude a entender tu negocio.</span>@error('descripcion')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                            <label class="mt-4 grid gap-1.5 text-sm font-semibold text-slate-700">Comentarios adicionales <span class="font-normal text-slate-500">(opcional)</span><textarea wire:model="comentarios" rows="3" class="rounded-xl border-slate-300 bg-white px-3.5 py-3 text-sm font-normal leading-6 text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:ring-indigo-600 @error('comentarios') border-red-400 @enderror" placeholder="¿Hay algo más que debamos saber?"></textarea>@error('comentarios')<span class="text-xs font-medium text-red-600">{{ $message }}</span>@enderror</label>
                        </section>

                        <div class="border-t border-slate-100 pt-5">
                            <label class="flex cursor-pointer items-start gap-3 text-sm leading-6 text-slate-600"><input type="checkbox" wire:model="privacidad" required class="mt-1 size-4 rounded border-slate-300 text-indigo-700 focus:ring-indigo-600"><span>Acepto que mis datos se utilicen para atender y dar seguimiento a esta solicitud.</span></label>
                            @error('privacidad')<p class="mt-1 pl-7 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" wire:loading.attr="disabled" wire:target="enviar" class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-indigo-700 px-6 font-bold text-white shadow-md shadow-indigo-900/15 transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                                <span wire:loading.remove wire:target="enviar">Enviar solicitud</span><span wire:loading wire:target="enviar">Enviando solicitud…</span><svg wire:loading.remove wire:target="enviar" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </button>
                            <p class="mt-3 text-center text-xs leading-5 text-slate-500">Tu solicitud queda pendiente de revisión. No se crea una empresa, suscripción o cargo automáticamente.</p>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
</main>

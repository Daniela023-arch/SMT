<x-layouts::auth.login :title="'Iniciar sesión'">
    <div class="w-full max-w-md">
        <div class="mb-7">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-950">Iniciar sesión</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Accede a tu espacio de trabajo MULTIVERSE.</p>
        </div>

        <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">No se pudo iniciar sesión.</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nombre@ferreteria.mx"
            />

            <div class="relative space-y-2">
                <label for="password" class="text-sm font-semibold text-slate-800">Contraseña</label>
                <flux:input
                    name="password"
                    id="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    class="pr-12"
                />
                <button
                    type="button"
                    data-password-toggle="password"
                    aria-controls="password"
                    aria-label="Mostrar contraseña"
                    aria-pressed="false"
                    class="absolute right-2 bottom-1.5 z-10 grid size-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>
                    </svg>
                    <svg data-password-icon="hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.6 10.6 0 0 1 12 6c6.1 0 9.5 6 9.5 6a15.3 15.3 0 0 1-3.2 3.8M6.2 6.2C3.8 7.7 2.5 12 2.5 12s3.4 6 9.5 6c1 0 1.9-.2 2.8-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                    </svg>
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:checkbox name="remember" label="Recordarme" :checked="old('remember')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 hover:text-indigo-900" href="{{ route('password.request') }}" wire:navigate>¿Olvidaste tu contraseña?</a>
                @endif
            </div>

            <flux:button variant="primary" type="submit" class="w-full !rounded-xl !bg-indigo-700 !py-3 !font-bold hover:!bg-indigo-800" data-test="login-button">
                Iniciar sesión
            </flux:button>
        </form>
    </div>
</x-layouts::auth.login>

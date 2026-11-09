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

            <div class="space-y-2">
                <label for="password" class="text-sm font-semibold text-slate-800">Contraseña</label>
                <flux:input
                    name="password"
                    id="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    viewable
                />
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

<x-layouts::auth.login :title="'Recuperar contraseña'">
    <div class="auth-recovery-enter w-full max-w-md">
        <div class="mb-7">
            <span class="auth-recovery-icon mb-5 grid size-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/><path d="M16.5 4.5a3 3 0 0 1 5 2.2v1"/></svg>
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-950">Recuperar contraseña</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Escribe el correo de tu cuenta y te enviaremos un enlace para crear una contraseña nueva.</p>
        </div>

        @if (session('status'))
            <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Si el correo corresponde a una cuenta activa, recibirás instrucciones para restablecer la contraseña.
            </div>
        @endif

        @if ($errors->has('email'))
            <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                No se pudo procesar ese correo. Revísalo e inténtalo de nuevo.
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-recovery-form space-y-5">
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

            <flux:button variant="primary" type="submit" class="auth-recovery-button w-full !rounded-xl !bg-indigo-700 !py-3 !font-bold hover:!bg-indigo-800" data-test="email-password-reset-link-button">
                Enviar enlace de recuperación
            </flux:button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            ¿Recordaste tu contraseña?
            <a href="{{ route('login') }}" wire:navigate class="ml-1 font-bold text-indigo-700 underline decoration-indigo-300 underline-offset-4 hover:text-indigo-900">Volver al inicio de sesión</a>
        </div>
    </div>
</x-layouts::auth.login>

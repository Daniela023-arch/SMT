<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Security settings') }}</flux:heading>

    @if (auth()->user()->debe_cambiar_password)
        <div role="alert" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <p class="font-bold">Antes de continuar, cambia tu contraseña temporal.</p>
            <p class="mt-1">Este paso es obligatorio para proteger tu cuenta. Después podrás personalizar tu tenant y continuar con su activación.</p>
        </div>
    @endif

    <x-settings.layout :heading="auth()->user()->debe_cambiar_password ? 'Cambia tu contraseña' : __('Update password')" :subheading="auth()->user()->debe_cambiar_password ? 'Ingresa la contraseña temporal y define una nueva contraseña para tu cuenta.' : __('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-password-button">{{ __('Save') }}</flux:button>
            </div>
        </form>


    </x-settings.layout>

</section>

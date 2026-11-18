<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Security settings')]
class Security extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void {}

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $cambioObligatorio = (bool) Auth::user()->debe_cambiar_password;

        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
            'debe_cambiar_password' => false,
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: $cambioObligatorio
            ? 'Contraseña actualizada. Ya puedes continuar con la configuración de tu tenant.'
            : __('Password updated.'));

        if ($cambioObligatorio) {
            $this->redirectRoute('onboarding.show', navigate: true);
        }
    }
}

<?php

namespace App\Livewire\Auth;

use App\Services\UserActivationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ActivateAccount extends Component
{
    #[Locked]
    public ?string $token = null;

    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function messages(): array
    {
        return [
            'email.required' => 'O endereço de email é obrigatório.',
            'email.email' => 'Introduza um endereço de email válido.',
            'password.required' => 'A palavra-passe é obrigatória.',
            'password.min' => 'A palavra-passe deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da palavra-passe não coincide.',
        ];
    }

    public function mount(?string $token = null): void
    {
        $this->token = $token;
    }

    public function activate(UserActivationService $activationService): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $activationService->activate($this->token ?? '', $this->email, $this->password);

        Auth::login($user);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirect('/maestro', navigate: true);
    }
}

<?php

namespace App\Filament\Pages\Auth;

use App\Http\Middleware\EnforceMfa;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attemptWhen(
            $this->getCredentialsFromFormData($data),
            fn (User $user): bool => $user->canAccessPanel(Filament::getCurrentPanel())
                && (! $user->hasTwoFactorEnabled() || $user->validateTwoFactorCode($data['two_factor_code'] ?? null)),
            $data['remember'] ?? false,
        )) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if ($user instanceof User && $user->hasTwoFactorEnabled()) {
            session()->put(EnforceMfa::SESSION_KEY, $user->getKey());
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getTwoFactorCodeFormComponent(): Component
    {
        return TextInput::make('two_factor_code')
            ->label('Código de autenticação')
            ->helperText('Introduza o código da aplicação autenticadora ou um código de recuperação.')
            ->autocomplete('one-time-code')
            ->extraInputAttributes(['tabindex' => 3]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getTwoFactorCodeFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'As credenciais ou o código de autenticação estão incorretos.',
        ]);
    }
}

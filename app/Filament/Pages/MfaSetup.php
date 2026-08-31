<?php

namespace App\Filament\Pages;

use App\Http\Middleware\EnforceMfa;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Lab404\Impersonate\Services\ImpersonateManager;

class MfaSetup extends Page
{
    protected static string $view = 'filament.pages.mfa-setup';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'configurar-autenticacao-multifator';

    public string $code = '';

    public string $regenerationCode = '';

    public ?string $qrCode = null;

    /** @var array<int, string> */
    public array $recoveryCodes = [];

    public static function canAccess(): bool
    {
        return ! app(ImpersonateManager::class)->isImpersonating();
    }

    public function mount(): void
    {
        abort_if(app(ImpersonateManager::class)->isImpersonating(), 403);

        /** @var User $user */
        $user = Filament::auth()->user();

        if (! $user->hasTwoFactorEnabled()) {
            if (! $user->twoFactorAuth()->exists()) {
                $user->createTwoFactorAuth();
            }

            $this->qrCode = $user->twoFactorAuth->toQr();
        }
    }

    public function confirm(): void
    {
        $this->validate(['code' => ['required', 'string']]);

        /** @var User $user */
        $user = Filament::auth()->user();

        if (! $user->confirmTwoFactorAuth($this->code)) {
            $this->addError('code', 'O código de autenticação é inválido.');

            return;
        }

        $this->recoveryCodes = $user->getRecoveryCodes()->pluck('code')->values()->all();
        $this->code = '';
        session()->put(EnforceMfa::SESSION_KEY, $user->getKey());

        Notification::make()
            ->success()
            ->title('Autenticação multifator ativada')
            ->send();
    }

    public function regenerateRecoveryCodes(): void
    {
        $this->validate(['regenerationCode' => ['required', 'string']]);

        /** @var User $user */
        $user = Filament::auth()->user();

        if (! $user->validateTwoFactorCode($this->regenerationCode, false)) {
            $this->addError('regenerationCode', 'O código de autenticação é inválido.');

            return;
        }

        $this->recoveryCodes = $user->generateRecoveryCodes()->pluck('code')->values()->all();
        $this->regenerationCode = '';

        Notification::make()
            ->success()
            ->title('Novos códigos de recuperação gerados')
            ->send();
    }

    public function getTitle(): string
    {
        return 'Autenticação multifator';
    }
}

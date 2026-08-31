<x-filament-widgets::widget>
    <x-filament::section class="border border-warning-300">
        <x-slot name="heading">Autenticação multifator pendente</x-slot>
        <p class="text-sm text-gray-700 dark:text-gray-300">
            Configure a autenticação multifator até <strong>{{ auth()->user()->mfa_grace_until->format('d/m/Y') }}</strong> para manter o acesso ao painel.
            <a class="font-medium text-primary-600 hover:underline" href="{{ \App\Filament\Pages\MfaSetup::getUrl() }}">Configurar agora</a>
        </p>
    </x-filament::section>
</x-filament-widgets::widget>

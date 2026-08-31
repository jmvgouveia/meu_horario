<x-filament-panels::page>
    <div class="space-y-6">
        @if (! auth()->user()->hasTwoFactorEnabled())
            <x-filament::section>
                <x-slot name="heading">Configure a autenticação multifator</x-slot>

                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    Digitalize o código QR com a sua aplicação autenticadora e introduza o código apresentado.
                </p>

                @if ($qrCode)
                    <div class="mb-6 flex justify-center rounded-lg bg-white p-4">{!! $qrCode !!}</div>
                @endif

                <form wire:submit="confirm" class="space-y-4">
                    <x-filament::input.wrapper :valid="! $errors->has('code')">
                        <x-filament::input wire:model="code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="Código de 6 dígitos" />
                    </x-filament::input.wrapper>
                    @error('code') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
                    <x-filament::button type="submit">Ativar autenticação multifator</x-filament::button>
                </form>
            </x-filament::section>
        @endif

        @if ($recoveryCodes)
            <x-filament::section>
                <x-slot name="heading">Códigos de recuperação</x-slot>

                <p class="mb-4 text-sm text-danger-600 dark:text-danger-400">Guarde estes códigos num local seguro. Não serão novamente apresentados.</p>
                <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-50 p-4 font-mono text-sm dark:bg-gray-900">
                    @foreach ($recoveryCodes as $recoveryCode)
                        <span>{{ $recoveryCode }}</span>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        @if (auth()->user()->hasTwoFactorEnabled())
            <x-filament::section>
                <x-slot name="heading">Gerar novos códigos de recuperação</x-slot>

                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Confirme com o código da aplicação autenticadora. Não é possível usar um código de recuperação nesta ação.</p>
                <form wire:submit="regenerateRecoveryCodes" class="space-y-4">
                    <x-filament::input.wrapper :valid="! $errors->has('regenerationCode')">
                        <x-filament::input wire:model="regenerationCode" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="Código de 6 dígitos" />
                    </x-filament::input.wrapper>
                    @error('regenerationCode') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
                    <x-filament::button type="submit" color="gray">Gerar novos códigos</x-filament::button>
                </form>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

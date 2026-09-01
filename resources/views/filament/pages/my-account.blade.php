<x-filament::page>
    <x-filament-panels::form wire:submit="save" class="max-w-2xl">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                Guardar alterações
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament::page>

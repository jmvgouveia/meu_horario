<x-filament-panels::page>

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div class="text-sm text-gray-500">
                <strong>Last checked:</strong> {{ $this->lastChecked ?? 'Nunca' }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border p-4">
                <div class="text-sm text-gray-500">Pacotes diretos</div>
                <div class="text-2xl font-semibold">{{ $this->stats['direct_count'] ?? 0 }}</div>
            </div>

            <div class="rounded-xl border p-4">
                <div class="text-sm text-gray-500">Pacotes dev</div>
                <div class="text-2xl font-semibold">{{ $this->stats['dev_count'] ?? 0 }}</div>
            </div>

            <div class="rounded-xl border p-4">
                <div class="text-sm text-gray-500">Updates disponíveis</div>
                <div class="text-2xl font-semibold">{{ $this->stats['outdated_count'] ?? 0 }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <x-filament::section heading="Sistema" class="xl:col-span-1">
                <div class="space-y-2 text-sm">
                    <div><strong>Aplicação:</strong> {{ $this->system['app_name'] }}</div>
                    <div><strong>Ambiente:</strong> {{ $this->system['app_env'] }}</div>
                    <div><strong>PHP:</strong> {{ $this->system['php_version'] }}</div>
                    <div><strong>Laravel:</strong> {{ $this->system['laravel_version'] }}</div>
                    <div><strong>Filament:</strong> {{ $this->system['filament_version'] }}</div>
                    <div><strong>Livewire:</strong> {{ $this->system['livewire_version'] }}</div>
                </div>
            </x-filament::section>

            <x-filament::section heading="Pacotes com updates" class="xl:col-span-2">
                <div class="space-y-3">
                    @forelse ($this->outdatedPackages as $package)
                    @php
                    $badgeColor = match ($package['status']) {
                    'Update major' => 'danger',
                    'Update minor/patch' => 'warning',
                    'Instalado' => 'success',
                    'Não instalado' => 'gray',
                    default => 'gray',
                    };
                    @endphp

                    <div class="rounded-xl border p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="font-semibold break-all">{{ $package['name'] }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    <strong>Constraint:</strong> {{ $package['constraint'] ?? '—' }}
                                </div>
                            </div>

                            <x-filament::badge :color="$badgeColor">
                                {{ $package['status'] }}
                            </x-filament::badge>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div><strong>Instalada:</strong> {{ $package['version'] ?? '—' }}</div>
                            <div><strong>Última disponível:</strong> {{ $package['latest'] ?? '—' }}</div>
                            <div><strong>Tipo:</strong> {{ $package['type'] }}</div>
                            <div><strong>Reference:</strong> {{ $package['reference'] ?? '—' }}</div>

                            @if (!empty($package['update_type']))
                            <div>
                                <strong>Tipo de update:</strong> {{ $package['update_type'] }}
                            </div>
                            @endif

                            @if (!empty($package['latest_status']))
                            <div>
                                <strong>Estado da versão:</strong> {{ $package['latest_status'] }}
                            </div>
                            @endif
                        </div>

                        @if (!empty($package['description']))
                        <div class="mt-3 text-sm text-gray-600">
                            <strong>Descrição:</strong> {{ $package['description'] }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">
                        Não existem updates registados.
                    </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Pacotes diretos (require)">
            <div class="space-y-3">
                @foreach ($this->directPackages as $package)
                @php
                $badgeColor = match ($package['status']) {
                'Instalado' => 'success',
                'Update minor/patch' => 'warning',
                'Update major' => 'danger',
                'Não instalado' => 'gray',
                default => 'gray',
                };
                @endphp

                <div class="rounded-xl border p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold break-all">{{ $package['name'] }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                <strong>Constraint:</strong> {{ $package['constraint'] ?? '—' }}
                            </div>
                        </div>

                        <x-filament::badge :color="$badgeColor">
                            {{ $package['status'] }}
                        </x-filament::badge>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div><strong>Versão instalada:</strong> {{ $package['version'] ?? '—' }}</div>
                        <div><strong>Reference:</strong> {{ $package['reference'] ?? '—' }}</div>

                        @if (!empty($package['latest']))
                        <div><strong>Última disponível:</strong> {{ $package['latest'] }}</div>
                        @endif

                        @if (!empty($package['update_type']))
                        <div><strong>Tipo de update:</strong> {{ $package['update_type'] }}</div>
                        @endif

                        @if (!empty($package['latest_status']))
                        <div><strong>Estado da versão:</strong> {{ $package['latest_status'] }}</div>
                        @endif
                    </div>

                    @if (!empty($package['description']))
                    <div class="mt-3 text-sm text-gray-600">
                        <strong>Descrição:</strong> {{ $package['description'] }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section heading="Pacotes de desenvolvimento (require-dev)">
            <div class="space-y-3">
                @foreach ($this->devPackages as $package)
                @php
                $badgeColor = match ($package['status']) {
                'Instalado' => 'success',
                'Update minor/patch' => 'warning',
                'Update major' => 'danger',
                'Não instalado' => 'gray',
                default => 'gray',
                };
                @endphp

                <div class="rounded-xl border p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold break-all">{{ $package['name'] }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                <strong>Constraint:</strong> {{ $package['constraint'] ?? '—' }}
                            </div>
                        </div>

                        <x-filament::badge :color="$badgeColor">
                            {{ $package['status'] }}
                        </x-filament::badge>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div><strong>Versão instalada:</strong> {{ $package['version'] ?? '—' }}</div>
                        <div><strong>Reference:</strong> {{ $package['reference'] ?? '—' }}</div>

                        @if (!empty($package['latest']))
                        <div><strong>Última disponível:</strong> {{ $package['latest'] }}</div>
                        @endif

                        @if (!empty($package['update_type']))
                        <div><strong>Tipo de update:</strong> {{ $package['update_type'] }}</div>
                        @endif

                        @if (!empty($package['latest_status']))
                        <div><strong>Estado da versão:</strong> {{ $package['latest_status'] }}</div>
                        @endif
                    </div>

                    @if (!empty($package['description']))
                    <div class="mt-3 text-sm text-gray-600">
                        <strong>Descrição:</strong> {{ $package['description'] }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
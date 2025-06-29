@php
$totalReducaoCargos= collect($resumo['cargos'] ?? [])->sum('redução_letiva');
$totalReducaoReducoes = collect($resumo['tempo_reducoes'] ?? [])->sum('redução_letiva');
@endphp



<div class="w-full overflow-x-auto rounded-lg ">
    <div class="p-4 bg-gray dark:bg-gray-800 rounded shadow">
        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Resumo de Carga Horária</h3>
        <br>
        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Horas por Marcar</h3>

        <div class="flex flex-wrap gap-4">


            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Horas letivas</h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-clock class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold !text-blue-600 dark:!text-blue-400">
                        &nbsp {{ $resumo['disponivel_letiva'] ?? 0 }} h
                    </p>
                </div>
            </div>

            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Horas não letivas</h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-clock class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold !text-blue-600 dark:!text-blue-400">
                        &nbsp {{ $resumo['disponivel_naoletiva'] ?? 0 }} h
                    </p>
                </div>
            </div>


        </div>
        <br>
        <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-100">Horas Marcadas</h3>

        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Horas Letivas</h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-academic-cap class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        &nbsp {{ $resumo['letiva'] ?? 0 }} h
                    </p>
                </div>
            </div>

            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Horas Não Letivas </h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-briefcase class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        &nbsp{{ $resumo['nao_letiva'] ?? 0 }} h
                    </p>
                </div>
            </div>

            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Total de Cargos</h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-user-group class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        &nbsp {{ $totalReducaoCargos }} h
                    </p>
                </div>
            </div>

            <div class="flex-1 min-w-[200px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                <h4 class="text-sm !text-blue-600 dark:!text-blue-400 m-0">Total de Reduções</h4>
                <div class="flex items-center space-x-2 mb-1">
                    <x-heroicon-o-minus-circle class="w-7 h-7 !text-blue-600 dark:!text-blue-400" />
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">
                        &nbsp{{ $totalReducaoReducoes }} h
                    </p>
                </div>
            </div>
        </div>

        <br>
        @if (!empty($resumo['cargos']))
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Cargos com Redução</h4>

            <div class="flex flex-wrap gap-4">
                @foreach ($resumo['cargos'] as $cargo)
                <div class="flex-1 min-w-[250px] bg-white dark:bg-gray-800 rounded-xl border shadow p-4">
                    <h5 class="text-md font-bold text-gray-800 dark:text-white mb-2">
                        {{ $cargo['nome'] }}
                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ $cargo['descricao'] }}
                    </p>
                    <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        redução de {{ $cargo['redução_letiva'] }} h
                    </div>
                    <!-- <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                    Não Letiva: {{ $cargo['redução_naoletiva'] }}h
                </div> -->
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <br>

        @if (!empty($resumo['tempo_reducoes']))
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Reduções por Tempo de Serviço</h4>

            <div class="flex flex-wrap gap-4">
                @foreach ($resumo['tempo_reducoes'] as $reducao)
                <div class="flex-1 min-w-[250px] bg-white dark:bg-gray-800 rounded-xl shadow border p-4">
                    <h5 class="text-md font-bold text-gray-800 dark:text-white mb-2">
                        {{ $reducao['nome'] }}

                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        {{ $reducao['descricao'] }}
                    </p>
                    <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        redução de {{ $reducao['redução_letiva'] }} h
                    </div>
                    <!-- <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                    Não Letiva: {{ $reducao['redução_naoletiva'] }}h
                </div> -->
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
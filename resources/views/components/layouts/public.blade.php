<!DOCTYPE html>
<html lang="pt-PT" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-50 font-sans text-gray-950 antialiased dark:bg-gray-950 dark:text-white">
        <div class="flex min-h-screen flex-col">
            <header class="border-b border-gray-200/80 bg-white/90 px-6 py-5 dark:border-white/10 dark:bg-gray-950/90">
                <div class="mx-auto flex w-full max-w-4xl items-center justify-between gap-4">
                    <a href="/maestro" aria-label="Ir para o Maestro" class="shrink-0">
                        <x-app-logo />
                    </a>
                    <a href="{{ url('/maestro/login') }}" class="text-sm font-medium text-[#063b82] underline-offset-4 transition hover:text-[#ffbf00] hover:underline dark:text-blue-300 dark:hover:text-[#ffbf00]">
                        Voltar ao início de sessão
                    </a>
                </div>
            </header>

            <main class="w-full flex-1 px-6 py-10 sm:py-14">
                <div class="mx-auto w-full max-w-4xl">
                    {{ $slot }}
                </div>
            </main>

            <x-public-footer />
        </div>
        @fluxScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-1 flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="mb-1 flex items-center justify-center">
                        <img
                            src="{{ asset('images/maestro-logo-light.svg') }}"
                            alt="Maestro"
                            class="h-28 w-auto object-contain dark:hidden"
                        />
                        <img
                            src="{{ asset('images/maestro-logo-dark.svg') }}"
                            alt="Maestro"
                            class="hidden h-28 w-auto object-contain dark:block"
                        />
                    </span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        <x-public-footer compact />
        @fluxScripts
    </body>
</html>

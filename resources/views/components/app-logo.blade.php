<img
    src="{{ asset('images/maestro-logo-light.svg') }}"
    alt="Maestro"
    class="h-10 w-auto max-w-full object-contain dark:hidden"
    {{ $attributes }}
/>
<img
    src="{{ asset('images/maestro-logo-dark.svg') }}"
    alt="Maestro"
    class="hidden h-10 w-auto max-w-full object-contain dark:block"
    {{ $attributes }}
/>

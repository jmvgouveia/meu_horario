<style>
    .logo-light {
        display: block;
    }

    .logo-dark {
        display: none;
    }

    .dark .logo-light,
    [data-theme="dark"] .logo-light,
    html.dark .logo-light {
        display: none !important;
    }

    .dark .logo-dark,
    [data-theme="dark"] .logo-dark,
    html.dark .logo-dark {
        display: block !important;
    }
</style>

@if (request()->is('meuhorario/login'))
<img src="{{ asset('images/logo-login.png') }}" alt="meuHorario Login" class="logo-light" style="height: 200px;">
<img src="{{ asset('images/logo-login-dark.png') }}" alt="meuHorario Login Dark" class="logo-dark" style="height: 200px;">
@else
<img src="{{ asset('images/logo-painel.png') }}" alt="meuHorario Painel" class="logo-light" style="height: 60px;">
<img src="{{ asset('images/logo-painel-dark.png') }}" alt="meuHorario Painel Dark" class="logo-dark" style="height: 60px;">
@endif
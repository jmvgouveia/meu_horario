@if (request()->is('meuhorario/login'))
<img src="{{ asset('images/logo-login.png') }}" alt="meuHorario Login" style="height: 200px;">
@else
<img src="{{ asset('images/logo-painel1.png') }}" alt="meuHorario Painel" style="height: 60px;">
@endif
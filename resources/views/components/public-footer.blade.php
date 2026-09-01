@props(['compact' => false])

<footer @class([
    'public-auth-footer' => $compact,
    'w-full px-6 text-center text-[11px] leading-4 text-gray-500 dark:text-gray-400',
])>
    <div class="mx-auto max-w-4xl space-y-0">
        <p class="text-gray-600 dark:text-gray-300">
            Conservatório – Escola das Artes da Madeira, Eng. Luiz Peter Clode
        </p>
        <nav aria-label="Ligações institucionais" class="flex flex-wrap items-center justify-center gap-x-2.5 gap-y-1 pt-1 text-gray-400 dark:text-gray-500">
            <span>© {{ now()->year }}</span>
            <span aria-hidden="true">·</span>
            <span>Versão: V.1</span>
            <span aria-hidden="true">·</span>
            <a href="{{ route('privacy') }}" class="text-[#063b82] transition hover:text-[#ffbf00] hover:underline dark:text-blue-300 dark:hover:text-[#ffbf00]">Privacidade</a>
            <span aria-hidden="true">·</span>
            <a href="{{ route('security') }}" class="text-[#063b82] transition hover:text-[#ffbf00] hover:underline dark:text-blue-300 dark:hover:text-[#ffbf00]">Segurança</a>
            <span aria-hidden="true">·</span>
            <a href="{{ route('support') }}" class="text-[#063b82] transition hover:text-[#ffbf00] hover:underline dark:text-blue-300 dark:hover:text-[#ffbf00]">Suporte</a>
        </nav>
    </div>
</footer>

@if (app('impersonate')->isImpersonating())
    <style>
        :root { --impersonate-banner-height: 50px; }
        html { margin-top: var(--impersonate-banner-height); }
        #impersonate-banner {
            position: fixed;
            top: 0;
            z-index: 45;
            display: flex;
            width: 100%;
            height: var(--impersonate-banner-height);
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            border-bottom: 1px solid #374151;
            background: #1f2937;
            color: #f3f4f6;
        }
        #impersonate-banner button {
            border-radius: 0.375rem;
            background: #f3f4f6;
            padding: 0.3rem 1rem;
            color: #1f2937;
            font-weight: 600;
        }
        #impersonate-banner button:hover { background: #ffffff; }
        .fi-topbar, div.fi-layout > aside.fi-sidebar { top: var(--impersonate-banner-height); }
        div.fi-layout > aside.fi-sidebar { height: calc(100vh - var(--impersonate-banner-height)); }
        @media print { html { margin-top: 0; } #impersonate-banner { display: none; } }
    </style>

    <div id="impersonate-banner">
        <span>A visualizar como <strong>{{ Filament\Facades\Filament::getUserName(Filament\Facades\Filament::auth()->user()) }}</strong> em modo de leitura.</span>
        <form method="POST" action="{{ route('impersonation.leave') }}">
            @csrf
            <button type="submit">Terminar visualização</button>
        </form>
    </div>
@endif

@php
    $user = filament()->auth()->user();
    $role = $user?->getRoleNames()->first();
@endphp

@if ($user)
    <div class="maestro-user-summary" aria-hidden="true">
        <span class="maestro-user-name">{{ $user->name }}</span>
        @if ($role)
            <span class="maestro-user-role">{{ $role }}</span>
        @endif
    </div>
@endif

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;
use Symfony\Component\HttpFoundation\Response;

class EnforceReadOnlyImpersonation
{
    /** @var array<int, string> */
    private const READ_ONLY_LIVEWIRE_METHODS = [
        '$refresh',
        'applyTableFilters',
        'gotoPage',
        'getFormState',
        'getFormSelectOptionLabel',
        'getFormSelectOptionLabels',
        'getFormSelectOptions',
        'getFormSelectSearchResults',
        'loadTable',
        'nextPage',
        'previousPage',
        'removeTableFilter',
        'removeTableFilters',
        'resetPage',
        'resetTable',
        'resetTableFiltersForm',
        'setActiveTab',
        'setPage',
        'sortTable',
        'toggleTableColumn',
        'update',
        'updateFormData',
        'updatedInteractsWithForms',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! app(ImpersonateManager::class)->isImpersonating()) {
            return $next($request);
        }

        abort_if($request->routeIs('verification.verify'), 403);

        if ($request->routeIs('impersonation.leave') || $request->isMethodSafe()) {
            return $next($request);
        }

        abort_unless($request->routeIs('default.livewire.update') && $this->isReadOnlyLivewireRequest($request), 403);

        return $next($request);
    }

    private function isReadOnlyLivewireRequest(Request $request): bool
    {
        foreach ($request->input('components', []) as $component) {
            foreach ($component['calls'] ?? [] as $call) {
                if (! in_array($call['method'] ?? null, self::READ_ONLY_LIVEWIRE_METHODS, true)) {
                    return false;
                }
            }
        }

        return true;
    }
}

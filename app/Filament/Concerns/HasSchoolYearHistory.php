<?php

namespace App\Filament\Concerns;

use App\Models\SchoolYear;
use Illuminate\Support\Collection;

trait HasSchoolYearHistory
{
    public static function activeSchoolYearId(): ?int
    {
        return SchoolYear::query()->where('active', true)->value('id');
    }

    public static function canBrowseSchoolYearHistory(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Secretaria']) ?? false;
    }

    /** @return Collection<int, string> */
    public static function schoolYearOptions(): Collection
    {
        $activeStartDate = SchoolYear::query()
            ->where('active', true)
            ->value('start_date');

        return SchoolYear::query()
            ->when($activeStartDate, fn ($query) => $query->where('start_date', '<=', $activeStartDate))
            ->orderByDesc('start_date')
            ->pluck('schoolyear', 'id');
    }

    public static function isActiveSchoolYear(?int $schoolYearId): bool
    {
        return $schoolYearId !== null && (int) $schoolYearId === (int) static::activeSchoolYearId();
    }
}

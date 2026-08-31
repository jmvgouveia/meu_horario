<?php

namespace App\Filament\Concerns;

use App\Models\SchoolYear;

trait HandlesSchoolYearHistory
{
    public function selectedSchoolYearId(): ?int
    {
        return $this->tableFilters['id_schoolyear']['value']
            ?? $this->getResource()::activeSchoolYearId();
    }

    public function isHistoricalMode(): bool
    {
        return ! $this->getResource()::isActiveSchoolYear($this->selectedSchoolYearId());
    }

    public function getSubheading(): ?string
    {
        if (! $this->isHistoricalMode()) {
            return null;
        }

        $schoolYear = SchoolYear::find($this->selectedSchoolYearId());

        return 'Modo de consulta — '.($schoolYear?->schoolyear ?? 'ano letivo selecionado');
    }
}

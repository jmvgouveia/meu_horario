<?php

namespace App\Models\Concerns;

use App\Models\Registration;
use App\Models\SchoolYear;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait BelongsToActiveRegistrationSchoolYear
{
    public static function bootBelongsToActiveRegistrationSchoolYear(): void
    {
        foreach (['creating', 'updating', 'deleting'] as $event) {
            static::{$event}(function (Model $model): void {
                $registrationId = $model->getAttribute('id_registration')
                    ?? $model->getOriginal('id_registration');
                $activeSchoolYearId = SchoolYear::query()->where('active', true)->value('id');
                $isActiveRegistration = Registration::query()
                    ->whereKey($registrationId)
                    ->where('id_schoolyear', $activeSchoolYearId ?? 0)
                    ->exists();

                if (! $isActiveRegistration) {
                    throw new AuthorizationException('As matrículas de anos anteriores estão disponíveis apenas para consulta.');
                }
            });
        }
    }
}

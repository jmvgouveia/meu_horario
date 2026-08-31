<?php

namespace App\Models\Concerns;

use App\Models\SchoolYear;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait BelongsToActiveSchoolYear
{
    public static function bootBelongsToActiveSchoolYear(): void
    {
        static::creating(function (Model $model): void {
            static::ensureActiveSchoolYear($model->getAttribute('id_schoolyear'));
        });

        static::updating(function (Model $model): void {
            static::ensureActiveSchoolYear($model->getOriginal('id_schoolyear'));
            static::ensureActiveSchoolYear($model->getAttribute('id_schoolyear'));
        });

        static::deleting(function (Model $model): void {
            static::ensureActiveSchoolYear($model->getAttribute('id_schoolyear'));
        });
    }

    private static function ensureActiveSchoolYear(mixed $schoolYearId): void
    {
        $activeSchoolYearId = SchoolYear::query()->where('active', true)->value('id');

        if (! $activeSchoolYearId || (int) $schoolYearId !== (int) $activeSchoolYearId) {
            throw new AuthorizationException('Os anos letivos anteriores estão disponíveis apenas para consulta.');
        }
    }
}

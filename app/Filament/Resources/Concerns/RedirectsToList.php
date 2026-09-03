<?php

namespace App\Filament\Resources\Concerns;

trait RedirectsToList
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

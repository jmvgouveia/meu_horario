<?php

namespace App\Filament\Resources\AboutSystemResource\Pages;

use App\Filament\Resources\AboutSystemResource;
use Filament\Resources\Pages\Page;

class AboutSystem extends Page
{
    protected static string $resource = AboutSystemResource::class;

    protected static string $view = 'filament.resources.about-system-resource.pages.about-system';
}

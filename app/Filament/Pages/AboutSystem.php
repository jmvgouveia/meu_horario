<?php

namespace App\Filament\Pages;

use App\Support\PackageVersionService;
use Composer\InstalledVersions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AboutSystem extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'About';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.about-system';

    public array $system = [];
    public array $directPackages = [];
    public array $devPackages = [];
    public array $outdatedPackages = [];
    public array $stats = [];
    public ?string $lastChecked = null;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $this->system = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'filament_version' => InstalledVersions::isInstalled('filament/filament')
                ? InstalledVersions::getPrettyVersion('filament/filament')
                : 'Não instalado',
            'livewire_version' => InstalledVersions::isInstalled('livewire/livewire')
                ? InstalledVersions::getPrettyVersion('livewire/livewire')
                : 'Não instalado',
        ];

        $this->directPackages = PackageVersionService::getDirectPackages();
        $this->devPackages = PackageVersionService::getDevPackages();
        $this->outdatedPackages = PackageVersionService::getOutdatedPackages();
        $this->stats = PackageVersionService::getStats();
        $this->lastChecked = PackageVersionService::getLastChecked();
    }

    public function refreshPackageScan(): void
    {
        try {
            PackageVersionService::refreshOutdatedScan();
            $this->loadData();

            Notification::make()
                ->title('Scan atualizado')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Falha ao atualizar scan')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshPackageScan')
                ->label('Atualizar scan')
                ->icon('heroicon-o-arrow-path')
                ->action('refreshPackageScan'),
        ];
    }
}

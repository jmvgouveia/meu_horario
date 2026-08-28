<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\TeacherResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\BuildingsOverview;
use App\Filament\Widgets\OverviewWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\StatsOverviewAP;
use App\Filament\Widgets\StatsOverviewRH;
use App\Filament\Widgets\StudentsOverview;
use App\Filament\Widgets\TeachersOverview;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('maestro')
            ->login()
            ->userMenuItems([
                MenuItem::make()
                    ->label('A Minha Conta')
                    ->url(function () {
                        $user = \Filament\Facades\Filament::auth()->user();

                        // Verifica se é professor e tem registo correspondente na tabela `teachers`
                        if ($user instanceof User && $user->hasRole('Professor') && $user->teacher) {
                            return TeacherResource::getUrl('edit', ['record' => $user->teacher->id]);
                        }

                        // Caso contrário, redireciona para edição do próprio user
                        return UserResource::getUrl('edit', ['record' => $user->id]);
                    })
                    ->icon('heroicon-o-user'),
            ])

            ->colors([
                'primary' => Color::hex('#063B82'),
            ])
            ->font('Inter')
            ->darkMode()
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarWidth('18rem')
            ->favicon(asset('images/maestro-symbol.svg'))
            ->assets([
                Css::make('maestro')->html(asset('css/maestro.css')),
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            //   ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                OverviewWidget::class,
                StatsOverview::class,
                StatsOverviewRH::class,
                StatsOverviewAP::class,
                TeachersOverview::class,
                StudentsOverview::class,
                BuildingsOverview::class,

                /* Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class, */
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('MAESTRO')
            ->brandLogo(asset('images/maestro-logo-light.svg'))
            ->darkModeBrandLogo(asset('images/maestro-logo-dark.svg'))
            ->brandLogoHeight(fn (): string => request()->is('maestro/login') ? '7rem' : '2.625rem')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Académico'),
                NavigationGroup::make()
                    ->label('Horários'),
                NavigationGroup::make()
                    ->label('Recursos'),
                NavigationGroup::make()
                    ->label('Administração')
                    ->collapsible(false),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s');
    }
}

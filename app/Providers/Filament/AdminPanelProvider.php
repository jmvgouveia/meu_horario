<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Resources\TeacherResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\BuildingsOverview;
use App\Filament\Widgets\OverviewWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\StatsOverviewAP;
use App\Filament\Widgets\StatsOverviewRH;
use App\Filament\Widgets\WeeklyScheduleWidget;
use App\Filament\Widgets\StudentsOverview;
use App\Filament\Widgets\TeachersOverview;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Filament\Facades\Filament as FacadesFilament;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Facades\FilamentAsset;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
//use Filament\Support\Facades\Filament;

use Filament\Notifications\NotificationsPlugin;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('meuhorario')
            ->login()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Minha Conta')
                    ->url(function () {
                        $user = \Filament\Facades\Filament::auth()->user();

                        // Verifica se é professor e tem registo correspondente na tabela `teachers`
                        if ($user instanceof User && $user->hasRole('Professor') && $user->teacher) {
                            return TeacherResource::getUrl('edit', ['record' => $user->teacher->id]);
                        }

                        // Caso contrário, redireciona para edição do próprio user
                        return UserResource::getUrl('edit', ['record' => $user->id]);
                    })
                    ->icon('heroicon-o-user')
            ])


            ->colors([
                'primary' => '#0094ee', // Cor do Texto
            ])
            ->favicon('images/favoicon.ico')

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            //   ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                WeeklyScheduleWidget::class,
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
            ->brandName('Meu Horário')
            ->brandLogo(function () {
                if (request()->is('meuhorario/login')) {
                    return '<img src="' . asset('images/logo-login.png') . '" style="height:64px;">';
                }

                return '<img src="' . asset('images/logo-painel.png') . '" style="height:32px;">';
            })
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Calendarização'),
                NavigationGroup::make()
                    ->label('Área do Professor'),
                NavigationGroup::make()
                    ->label('Área do Aluno'),
                NavigationGroup::make()
                    ->label('Gestão'),
                NavigationGroup::make()
                    ->label('Pólos e Núcleos'),
                NavigationGroup::make()
                    ->label('Administração')
                    ->collapsible(false),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s');;
    }
}

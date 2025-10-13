<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Widgets\StatsOverView;
use App\Livewire\FacingWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class InsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->viteTheme('resources/css/filament/ins/theme.css')
            ->login()
            ->brandName('نظام الوسيط (ماركت)')
            ->profile(EditProfile::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->maxContentWidth('Full')
            ->id('ins')
            ->path('ins')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Ins/Resources'), for: 'App\Filament\Ins\Resources')
            ->discoverPages(in: app_path('Filament/Ins/Pages'), for: 'App\Filament\Ins\Pages')
            ->pages([
               //
            ])
            ->discoverWidgets(in: app_path('Filament/Ins/Widgets'), for: 'App\Filament\Ins\Widgets')
            ->widgets([
                StatsOverView::class,
                FacingWidget::class,
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
            ]);
    }
}

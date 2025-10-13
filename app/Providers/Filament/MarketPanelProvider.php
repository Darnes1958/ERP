<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Widgets\StatsOverView;
use App\Livewire\FacingWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
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

class MarketPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->viteTheme('resources/css/filament/market/theme.css')
            ->login()
            ->brandName('نظام الوسيط (ماركت)')
            ->profile(EditProfile::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->maxContentWidth('Full')
            ->id('market')
            ->path('market')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Market/Resources'), for: 'App\Filament\Market\Resources')
            ->discoverPages(in: app_path('Filament/Market/Pages'), for: 'App\Filament\Market\Pages')
            ->pages([
                //
            ])
            ->discoverWidgets(in: app_path('Filament/Market/Widgets'), for: 'App\Filament\Market\Widgets')
            ->widgets([
                StatsOverView::class,
                FacingWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('فواتير شراء'),
                NavigationGroup::make('فواتير مبيعات'),
                NavigationGroup::make('ايصالات قبض ودفع'),
                NavigationGroup::make('زبائن وموردين'),
                NavigationGroup::make('مصارف وخزائن'),
                NavigationGroup::make('مصروفات'),
                NavigationGroup::make('مخازن و أصناف'),
                NavigationGroup::make('الحركة اليومية'),

                NavigationGroup::make('تحويلات بين الخزائن والمصارف'),
                NavigationGroup::make('إيجارات'),
                NavigationGroup::make('مرتبات'),
                NavigationGroup::make('الارباح'),
                NavigationGroup::make('اعدادات'),


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

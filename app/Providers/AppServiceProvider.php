<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

      FilamentView::registerRenderHook(
        'panels::page.end',
        fn (): View => view('analytics'),
        scopes: \App\Filament\Resources\BuysWorkResource::class,
      );

      Filament::registerNavigationGroups([
        'فواتير شراء',
        'فواتير مبيعات',
        'ايصالات قبض ودفع',
        'زبائن وموردين',
        'تقارير',
      ]);
      FilamentColor::register([
        'Fuchsia' =>  Color::Fuchsia,
        'green' =>  Color::Green,
        'blue' =>  Color::Blue,
        'gray' =>  Color::Gray,
      ]);

      FilamentAsset::register([
        \Filament\Support\Assets\Js::make('example-external-script', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js'),

      ]);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar','en']) // also accepts a closure
                ->displayLocale('ar');
        });
        Model::unguard();
    }
}

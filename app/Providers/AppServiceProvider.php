<?php

namespace App\Providers;

use App\Filament\Resources\BuysWorkResource;
use App\Filament\Resources\SellWorkResource;
use App\Filament\Resources\BuyResource;
use App\Filament\Resources\SellResource;
use Filament\Support\Assets\Js;
use App\Models\GlobalSetting;
use App\Models\Setting;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;


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

        Pdf::default()
            ->footerView('PDF.footer')
            ->withBrowsershot(function (Browsershot $shot) {
                $shot->noSandbox()
                    ->setChromePath(GlobalSetting::first()->exePath);
            })
            ->margins(10, 10, 20, 10, );
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        Table::configureUsing(fn(Table $table) => $table->defaultNumberLocale('nl'));
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn (): string => Blade::render('@livewire(\'top-bar\')'),
        );
      FilamentView::registerRenderHook(
        'panels::page.end',
        fn (): View => view('analytics'),
        scopes: [
            BuysWorkResource::class,
            SellWorkResource::class,
            BuyResource::class,
            SellResource::class,

            ]
      );

      FilamentColor::register([
        'Fuchsia' =>  Color::Fuchsia,
        'green' =>  Color::Green,
        'blue' =>  Color::Blue,
        'gray' =>  Color::Gray,
      ]);

      FilamentAsset::register([
        Js::make('example-external-script', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js'),

      ]);

        Model::unguard();
    }
}

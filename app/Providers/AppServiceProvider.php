<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;
use Illuminate\Support\ServiceProvider;

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
      FilamentAsset::register([
        \Filament\Support\Assets\Js::make('example-external-script', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js'),

      ]);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar','en']); // also accepts a closure
        });
        Model::unguard();
    }
}

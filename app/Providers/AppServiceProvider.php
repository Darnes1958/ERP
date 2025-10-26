<?php

namespace App\Providers;

use App\Filament\Ins\Pages\InpHafithaTran;
use App\Filament\ins\Pages\KsmKst;
use App\Filament\ins\Pages\newCont;
use App\Filament\ins\Resources\MainResource;
use App\Filament\market\Resources\BuyResource;
use App\Filament\market\Resources\BuysWorkResource;
use App\Filament\market\Resources\SellResource;
use App\Filament\market\Resources\SellWorkResource;
use App\Models\GlobalSetting;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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

        Table::configureUsing(fn(Table $table) => $table
            ->defaultNumberLocale('nl')
            ->emptyStateHeading('لا توجد بيانات')
            ->defaultKeySort(false)
        );

        CreateAction::configureUsing(fn(CreateAction $createAction) => $createAction->label('إضافة'));

        Radio::configureUsing(function (Radio $radio): void {
            $radio->inline()->inlineLabel()->translateLabel();
        });
        TextInput::configureUsing(function (TextInput $input): void {
            $input->translateLabel();
        });
        DatePicker::configureUsing(function (DatePicker $input): void {
            $input->translateLabel();
        });

        TextColumn::configureUsing(function (TextColumn $column): void {
            $column->translateLabel();
        });
        IconColumn::configureUsing(function (IconColumn $column): void {
            $column->translateLabel();
        });
        Select::configureUsing(function (Select $column): void {
            $column->translateLabel();
        });
        TextEntry::configureUsing(function (TextEntry $entry): void {$entry->translateLabel();});
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
            KsmKst::class,
            MainResource::class,
            newCont::class,
            InpHafithaTran::class,
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
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn (): string => Blade::render('@livewire(\'panel-change\')'),
        );
        RichEditor::configureUsing(function (RichEditor $richEditor):void {
            $richEditor->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                ['h1','h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd','alignJustify','textColor'],
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList','lead'],
                ['customBlocks','mergeTags'],
                ['table', 'attachFiles'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.
                ['undo', 'redo'],
                ['grid','gridDelete','details','horizontalRule','highlight']
            ]);
        });

        Model::unguard();
    }
}

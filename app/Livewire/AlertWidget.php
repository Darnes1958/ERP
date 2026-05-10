<?php

namespace App\Livewire;

use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\TitleAndSub;
use App\Models\GlobalSetting;
use App\Models\Setting;
use App\Models\User;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AlertWidget extends Widget implements HasForms
{
    use InteractsWithForms;
    protected string $view = 'livewire.alert-widget';

    protected static ?int $sort=3;
    protected int | string | array $columnSpan='full';

    public function form(Schema $schema): Schema
{
    return $schema
        ->model(Setting::where('company',Auth::user()->company)->first())
        ->components([
            TextEntry::make('message1')
                ->hiddenLabel()
                ->state(fn ($record): string =>
                RichContentRenderer::make($record->alertMessage)
                    ->customBlocks([
                        TitleAndSub::class,
                    ],)
                    ->toHtml()
                )
                ->prose(),
        ]);
}
}

<?php

namespace App\Livewire;

use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\TitleAndSub;
use App\Models\GlobalSetting;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class FacingWidget extends Widget implements  HasForms
{
    use InteractsWithForms;

    protected string $view = 'livewire.facing-widget';
    protected static ?int $sort=2;
    protected int | string | array $columnSpan='full';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(GlobalSetting::query()->first())
            ->components([
                TextEntry::make('message1')
                    ->hiddenLabel()
                    ->state(fn ($record): string =>
                        RichContentRenderer::make($record->message1)
                        ->customBlocks([
                            TitleAndSub::class,
                        ],)
                        ->toHtml()
                    )
                    ->prose(),
            ]);
    }


}

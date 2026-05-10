<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\TitleAndSub;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('has_exp')
                    ->required(),
                Toggle::make('has_two')
                    ->required(),
                Toggle::make('many_place')
                    ->required(),
                Toggle::make('jomla')
                    ->required(),
                Toggle::make('barcode')
                    ->required(),
                Toggle::make('is_together'),
                Toggle::make('price_update'),
                RichEditor::make('userMessage')->customBlocks([
                    TitleAndSub::class,
                ]),

            ]);
    }
}

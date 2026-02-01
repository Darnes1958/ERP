<?php

namespace App\Filament\Market\Resources\InventoryData\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Tiptap\Nodes\Text;

class InventoryDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                 ->schema([
                     TextInput::make('data')
                         ->label('البيان')
                         ->belowLabel('شرح مختصر لاسباب عملية لجرد')
                         ->required(),
                     TextInput::make('notes')->belowLabel('إذا كان هناك اي ملاحظات'),
                     Hidden::make('user_id')->default(auth()->id()),

                 ])->columns(1)
            ]);
    }
}

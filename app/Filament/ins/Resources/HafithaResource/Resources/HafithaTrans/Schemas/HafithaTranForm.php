<?php

namespace App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HafithaTranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('acc')
                    ->required(),
                TextInput::make('kst')
                    ->required()
                    ->numeric(),
                DatePicker::make('ksm_date')
                    ->required(),
                TextInput::make('ksm_notes')   ,
            ]);
    }
}

<?php

namespace App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HafithaTranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('taj_id')
                    ->numeric(),
                TextEntry::make('acc'),
                TextEntry::make('kst')
                    ->numeric(),
                TextEntry::make('ksm_date')
                    ->date(),
                TextEntry::make('ksm_notes'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

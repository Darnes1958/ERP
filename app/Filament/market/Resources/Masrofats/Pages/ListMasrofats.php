<?php

namespace App\Filament\market\Resources\Masrofats\Pages;

use App\Filament\market\Resources\Masrofats\MasrofatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasrofats extends ListRecords
{
    protected static string $resource = MasrofatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()

            ->label('إضافة'),
        ];
    }
}

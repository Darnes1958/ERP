<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use App\Filament\Resources\MasrofatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasrofats extends ListRecords
{
    protected static string $resource = MasrofatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()

            ->label('إضافة'),
        ];
    }
}

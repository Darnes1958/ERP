<?php

namespace App\Filament\market\Resources\MasrTypeResource\Pages;

use App\Filament\market\Resources\MasrTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasrTypes extends ListRecords
{
    protected static string $resource = MasrTypeResource::class;
    protected ?string $heading='انواع المصروفات';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة'),
        ];
    }
}

<?php

namespace App\Filament\Resources\MasrTypeResource\Pages;

use App\Filament\Resources\MasrTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasrTypes extends ListRecords
{
    protected static string $resource = MasrTypeResource::class;
    protected ?string $heading='انواع المصروفات';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة'),
        ];
    }
}

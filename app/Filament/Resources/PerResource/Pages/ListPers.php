<?php

namespace App\Filament\Resources\PerResource\Pages;

use App\Filament\Resources\PerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPers extends ListRecords
{
    protected static string $resource = PerResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ادخال اذن صرف'),
        ];
    }
}

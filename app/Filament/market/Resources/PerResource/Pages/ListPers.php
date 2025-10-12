<?php

namespace App\Filament\market\Resources\PerResource\Pages;

use App\Filament\market\Resources\PerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPers extends ListRecords
{
    protected static string $resource = PerResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ادخال اذن صرف'),
        ];
    }
}

<?php

namespace App\Filament\ins\Resources\HafithaResource\Pages;

use App\Filament\ins\Resources\HafithaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHafithas extends ListRecords
{
    protected static string $resource = HafithaResource::class;
    protected ?string $heading='الحوافظ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('اضافة'),
        ];
    }
}

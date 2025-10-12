<?php

namespace App\Filament\ins\Resources\OverkstResource\Pages;

use App\Filament\ins\Resources\OverkstResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOverksts extends ListRecords
{
    protected static string $resource = OverkstResource::class;
    protected ?string $heading='أقساط مخصومة بالفائض';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('اضافة'),
        ];

    }


}

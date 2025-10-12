<?php

namespace App\Filament\ins\Resources\TajResource\Pages;

use App\Filament\ins\Resources\TajResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTajs extends ListRecords
{
    protected static string $resource = TajResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة'),
        ];
    }
}

<?php

namespace App\Filament\market\Resources\AccResource\Pages;

use App\Filament\market\Resources\AccResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccs extends ListRecords
{
    protected static string $resource = AccResource::class;
    protected ?string $heading='حسابات مصرفية';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('اضافة مصرف'),
        ];
    }
}

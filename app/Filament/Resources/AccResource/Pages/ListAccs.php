<?php

namespace App\Filament\Resources\AccResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\AccResource;
use Filament\Actions;
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

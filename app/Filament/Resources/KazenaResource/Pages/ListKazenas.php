<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\KazenaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKazenas extends ListRecords
{
    protected static string $resource = KazenaResource::class;
    protected ?string $heading='خزائن';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('إضافة'),
        ];
    }
}

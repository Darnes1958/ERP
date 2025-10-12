<?php

namespace App\Filament\market\Resources\KazenaResource\Pages;

use App\Filament\market\Resources\KazenaResource;
use Filament\Actions\CreateAction;
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

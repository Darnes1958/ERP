<?php

namespace App\Filament\market\Resources\Places\Pages;

use App\Enums\AccRef;
use App\Filament\market\Resources\Places\PlaceResource;
use App\Livewire\Traits\AccTrait;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlaces extends ListRecords
{

    protected static string $resource = PlaceResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ادخال'),

        ];
    }
}

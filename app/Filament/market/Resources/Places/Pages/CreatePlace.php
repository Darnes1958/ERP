<?php

namespace App\Filament\market\Resources\Places\Pages;

use App\Enums\AccRef;
use App\Filament\market\Resources\Places\PlaceResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hall;
use Filament\Resources\Pages\CreateRecord;

class CreatePlace extends CreateRecord
{

    protected static string $resource = PlaceResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

}

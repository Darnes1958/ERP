<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\PlaceResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hall;
use App\Models\Place;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlace extends CreateRecord
{

    protected static string $resource = PlaceResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

}

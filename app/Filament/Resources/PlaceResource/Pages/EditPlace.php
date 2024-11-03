<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use App\Models\Hall;
use App\Models\Place;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlace extends EditRecord
{
    protected static string $resource = PlaceResource::class;
    protected ?string $heading='';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

}

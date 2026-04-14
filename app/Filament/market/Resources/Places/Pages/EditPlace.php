<?php

namespace App\Filament\market\Resources\Places\Pages;

use App\Filament\market\Resources\Places\PlaceResource;
use App\Models\Hall;
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

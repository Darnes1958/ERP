<?php

namespace App\Filament\market\Resources\PlaceResource\Pages;

use App\Filament\market\Resources\PlaceResource;
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

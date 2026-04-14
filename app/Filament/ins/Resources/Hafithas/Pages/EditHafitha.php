<?php

namespace App\Filament\ins\Resources\Hafithas\Pages;

use App\Filament\ins\Resources\Hafithas\HafithaResource;
use Filament\Resources\Pages\EditRecord;

class EditHafitha extends EditRecord
{
    protected static string $resource = HafithaResource::class;

    protected ?string $heading='';
}

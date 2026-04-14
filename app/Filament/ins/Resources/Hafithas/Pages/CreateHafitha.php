<?php

namespace App\Filament\ins\Resources\Hafithas\Pages;

use App\Filament\ins\Resources\Hafithas\HafithaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHafitha extends CreateRecord
{
    protected static string $resource = HafithaResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;

}

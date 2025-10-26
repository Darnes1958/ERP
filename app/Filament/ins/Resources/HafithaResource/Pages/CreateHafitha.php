<?php

namespace App\Filament\ins\Resources\HafithaResource\Pages;

use App\Filament\ins\Resources\HafithaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHafitha extends CreateRecord
{
    protected static string $resource = HafithaResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;

}

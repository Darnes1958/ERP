<?php

namespace App\Filament\ins\Resources\TajResource\Pages;

use App\Filament\ins\Resources\TajResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaj extends CreateRecord
{
    protected static string $resource = TajResource::class;
    protected ?string $heading='';
}

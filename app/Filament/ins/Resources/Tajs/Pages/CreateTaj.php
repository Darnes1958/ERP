<?php

namespace App\Filament\ins\Resources\Tajs\Pages;

use App\Filament\ins\Resources\Tajs\TajResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaj extends CreateRecord
{
    protected static string $resource = TajResource::class;
    protected ?string $heading='';
}

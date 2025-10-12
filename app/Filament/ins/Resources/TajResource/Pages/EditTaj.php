<?php

namespace App\Filament\ins\Resources\TajResource\Pages;

use App\Filament\ins\Resources\TajResource;
use Filament\Resources\Pages\EditRecord;

class EditTaj extends EditRecord
{
    protected static string $resource = TajResource::class;
    protected ?string $heading='';

}

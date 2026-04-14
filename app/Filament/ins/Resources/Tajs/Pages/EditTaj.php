<?php

namespace App\Filament\ins\Resources\Tajs\Pages;

use App\Filament\ins\Resources\Tajs\TajResource;
use Filament\Resources\Pages\EditRecord;

class EditTaj extends EditRecord
{
    protected static string $resource = TajResource::class;
    protected ?string $heading='';

}

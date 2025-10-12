<?php

namespace App\Filament\ins\Resources\WrongkstResource\Pages;

use App\Filament\ins\Resources\WrongkstResource;
use Filament\Resources\Pages\ListRecords;

class ListWrongksts extends ListRecords
{
    protected static string $resource = WrongkstResource::class;

    protected ?string $heading='أقساط واردة بالخطأ';
}

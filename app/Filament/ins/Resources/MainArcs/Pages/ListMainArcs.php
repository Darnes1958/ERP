<?php

namespace App\Filament\ins\Resources\MainArcs\Pages;

use App\Filament\ins\Resources\MainArcs\MainArcResource;
use Filament\Resources\Pages\ListRecords;


class ListMainArcs extends ListRecords
{
    protected static string $resource = MainArcResource::class;

    protected ?string $heading='استفسار عن الأرشيف';

}

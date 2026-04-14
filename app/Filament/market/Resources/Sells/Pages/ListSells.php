<?php

namespace App\Filament\market\Resources\Sells\Pages;

use App\Filament\market\Resources\Sells\SellResource;
use Filament\Resources\Pages\ListRecords;

class ListSells extends ListRecords
{
    protected static string $resource = SellResource::class;

    protected ?string $heading="";
}

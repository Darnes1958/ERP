<?php

namespace App\Filament\market\Resources\SellOffers\Pages;

use App\Filament\market\Resources\SellOffers\SellOfferResource;
use Filament\Resources\Pages\ListRecords;

class ListSellOffers extends ListRecords
{
    protected static string $resource = SellOfferResource::class;

    protected ?string $heading = '';
}

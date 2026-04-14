<?php

namespace App\Filament\market\Resources\Suppliers\Pages;

use App\Filament\market\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
  protected ?string $heading="";
    protected static string $resource = SupplierResource::class;
}

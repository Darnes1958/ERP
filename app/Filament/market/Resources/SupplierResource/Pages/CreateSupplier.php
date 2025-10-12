<?php

namespace App\Filament\market\Resources\SupplierResource\Pages;

use App\Filament\market\Resources\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
  protected ?string $heading="";
    protected static string $resource = SupplierResource::class;
}

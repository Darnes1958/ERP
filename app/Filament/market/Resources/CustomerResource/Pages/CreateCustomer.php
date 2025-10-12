<?php

namespace App\Filament\market\Resources\CustomerResource\Pages;

use App\Filament\market\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
  protected ?string $heading="";
    protected static string $resource = CustomerResource::class;
}

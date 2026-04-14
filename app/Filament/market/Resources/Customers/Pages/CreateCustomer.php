<?php

namespace App\Filament\market\Resources\Customers\Pages;

use App\Filament\market\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
  protected ?string $heading="";
    protected static string $resource = CustomerResource::class;
}

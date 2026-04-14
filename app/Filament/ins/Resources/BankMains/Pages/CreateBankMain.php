<?php

namespace App\Filament\ins\Resources\BankMains\Pages;

use App\Filament\ins\Resources\BankMains\BankMainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankMain extends CreateRecord
{
    protected ?string $heading='';
    protected static string $resource = BankMainResource::class;
}

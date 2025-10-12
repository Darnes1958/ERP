<?php

namespace App\Filament\ins\Resources\BankMainResource\Pages;

use App\Filament\ins\Resources\BankMainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankMain extends CreateRecord
{
    protected ?string $heading='';
    protected static string $resource = BankMainResource::class;
}

<?php

namespace App\Filament\ins\Resources\BankMains\Pages;

use App\Filament\ins\Resources\BankMains\BankMainResource;
use Filament\Resources\Pages\EditRecord;

class EditBankMain extends EditRecord
{
    protected static string $resource = BankMainResource::class;
    protected ?string $heading='';


}

<?php

namespace App\Filament\market\Resources\BuysWorkResource\Pages;

use App\Filament\market\Resources\BuysWorkResource;
use Filament\Resources\Pages\EditRecord;

class EditBuysWork extends EditRecord
{
    protected static string $resource = BuysWorkResource::class;

    protected ?string $heading='تعديل فاتورة شراء';
}

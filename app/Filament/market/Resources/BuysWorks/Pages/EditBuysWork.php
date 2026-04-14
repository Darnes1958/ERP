<?php

namespace App\Filament\market\Resources\BuysWorks\Pages;

use App\Filament\market\Resources\BuysWorks\BuysWorkResource;
use Filament\Resources\Pages\EditRecord;

class EditBuysWork extends EditRecord
{
    protected static string $resource = BuysWorkResource::class;

    protected ?string $heading='تعديل فاتورة شراء';
}

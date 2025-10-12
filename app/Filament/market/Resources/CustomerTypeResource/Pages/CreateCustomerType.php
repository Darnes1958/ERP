<?php

namespace App\Filament\market\Resources\CustomerTypeResource\Pages;

use App\Filament\market\Resources\CustomerTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerType extends CreateRecord
{
    protected static string $resource = CustomerTypeResource::class;
    protected ?string $heading='إضافة تصنيف زبائن';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

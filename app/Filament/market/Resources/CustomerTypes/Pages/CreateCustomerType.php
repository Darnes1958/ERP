<?php

namespace App\Filament\market\Resources\CustomerTypes\Pages;

use App\Filament\market\Resources\CustomerTypes\CustomerTypeResource;
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

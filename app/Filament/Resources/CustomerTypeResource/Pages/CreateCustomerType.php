<?php

namespace App\Filament\Resources\CustomerTypeResource\Pages;

use App\Filament\Resources\CustomerTypeResource;
use Filament\Actions;
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

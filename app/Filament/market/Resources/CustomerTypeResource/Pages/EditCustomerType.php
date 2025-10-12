<?php

namespace App\Filament\market\Resources\CustomerTypeResource\Pages;

use App\Filament\market\Resources\CustomerTypeResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerType extends EditRecord
{
    protected static string $resource = CustomerTypeResource::class;
    protected ?string $heading='تعديل تصنيف';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()


                ->hidden(function (){return Customer::where('customer_type_id', $this->getRecord()->id)->count()>0; }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

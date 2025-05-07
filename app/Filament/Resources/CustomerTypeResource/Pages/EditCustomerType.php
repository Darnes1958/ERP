<?php

namespace App\Filament\Resources\CustomerTypeResource\Pages;

use App\Filament\Resources\CustomerTypeResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCustomerType extends EditRecord
{
    protected static string $resource = CustomerTypeResource::class;
    protected ?string $heading='تعديل تصنيف';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()


                ->hidden(function (){return Customer::where('customer_type_id', $this->getRecord()->id)->count()>0; }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

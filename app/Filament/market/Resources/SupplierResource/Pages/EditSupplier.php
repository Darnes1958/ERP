<?php

namespace App\Filament\market\Resources\SupplierResource\Pages;

use App\Filament\market\Resources\SupplierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;
  protected ?string $heading="";
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(Auth::user()->can('الغاء موردين')),
        ];
    }
}

<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;
  protected ?string $heading="";
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(Auth::user()->can('الغاء موردين')),
        ];
    }
}

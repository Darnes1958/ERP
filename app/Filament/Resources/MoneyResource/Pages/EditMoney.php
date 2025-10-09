<?php

namespace App\Filament\Resources\MoneyResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\MoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMoney extends EditRecord
{
    protected static string $resource = MoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(Auth::user()->can('الغاء تحويل')),
        ];
    }

}

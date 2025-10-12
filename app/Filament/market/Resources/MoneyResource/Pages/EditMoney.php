<?php

namespace App\Filament\market\Resources\MoneyResource\Pages;

use App\Filament\market\Resources\MoneyResource;
use Filament\Actions\DeleteAction;
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

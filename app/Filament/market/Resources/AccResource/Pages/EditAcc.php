<?php

namespace App\Filament\market\Resources\AccResource\Pages;

use App\Filament\market\Resources\AccResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAcc extends EditRecord
{
    protected static string $resource = AccResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(Auth::user()->can('الغاء مصارف')),
        ];
    }
}

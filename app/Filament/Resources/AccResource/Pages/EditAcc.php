<?php

namespace App\Filament\Resources\AccResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AccResource;
use Filament\Actions;
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

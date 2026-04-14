<?php

namespace App\Filament\market\Resources\Kazenas\Pages;

use App\Filament\market\Resources\Kazenas\KazenaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKazena extends EditRecord
{
    protected static string $resource = KazenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible( Auth::user()->can('الغاء مصارف')),
        ];
    }
}

<?php

namespace App\Filament\market\Resources\Masrofats\Pages;

use App\Filament\market\Resources\Masrofats\MasrofatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMasrofat extends EditRecord
{
    protected static string $resource = MasrofatResource::class;

    protected ?string $heading='تعديل مصروفات';
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(Auth::user()->can('الغاء مصروفات')),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\market\Resources\UserResource\Pages;

use App\Filament\market\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('اضافة مستخدم جديد'),
        ];
    }
}

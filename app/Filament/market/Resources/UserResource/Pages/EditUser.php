<?php

namespace App\Filament\market\Resources\UserResource\Pages;

use App\Filament\market\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected ?string $heading='تعديل بيانات مستخدم';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(Auth::id()==1),
        ];
    }
}

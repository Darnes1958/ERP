<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Sell;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected ?string $heading='تعديل بيانات مستخدم';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(Auth::id()==1),
        ];
    }
}

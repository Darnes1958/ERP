<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company']=Auth::user()->company;
        return parent::mutateFormDataBeforeCreate($data); // TODO: Change the autogenerated stub
    }
}
